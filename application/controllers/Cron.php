<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

	const BACKFILL_START_DATE = '2026-02-27';

	// Ceiling on how many pages of statesavings.ie's "global" location filter
	// _repair_missing_locations() will fetch for a single (draw, prize tier)
	// pair in one run. Real observed "global" buckets are well under this (a
	// few pages per draw across all tiers combined), so this only ever bites
	// a pair that can never fully resolve (e.g. a genuinely blank source
	// location for one row) — without it, that one pair could consume an
	// entire run's budget every run, starving every other pending pair.
	const MAX_REPAIR_PAGES_PER_PAIR = 20;

	// Now that backfill is complete, most cron runs have nothing new to find —
	// a new draw is only ever confirmed roughly once a week. Checking
	// statesavings.ie's results listing and homepage banner every single
	// minute (if that's the cron's schedule) is needless load on their site
	// for essentially always-empty results. Only actually check live once
	// this long has passed since the last check — unless a draw is mid-import
	// (see the auto_import=1-but-not-yet-published exception in
	// import_results()), in which case we always check, every run, to keep
	// that import moving.
	const LIVE_CHECK_INTERVAL_SECONDS = 1800; // 30 minutes

	// Ceiling on how many calendar dates _confirm_available_draws() will probe
	// in a single live check when looking for the next real draw. Draws are
	// normally exactly 7 days apart, but a holiday (e.g. Good Friday) can shift
	// that by a few days — see git history around 2026-08-31 — so we scan a
	// small window day-by-day rather than only ever checking +7 days. Bounded
	// so a long gap (e.g. the 2026-09 Radware outage — see that function's
	// docblock) can't consume a whole run's budget in one call; the cached
	// "confirmed no draw through" date in cron_state means the remaining days
	// pick up on the next live check instead of being re-scanned.
	const MAX_DRAW_SCAN_DAYS = 10;

	function __construct()
	{
		parent::__construct();
		$this->config->load('cron');
		$this->load->helper('statesavings');
	}

	public function import_results()
	{
		header('Content-Type: text/plain');
		set_time_limit(120); // this run can take ~45-60s; don't rely on hosting's default (often 30s)

		if ($this->input->get('key') !== $this->config->item('cron_secret')) {
			http_response_code(403);
			echo "Forbidden\n";
			return;
		}

		// Prevent overlapping runs if a previous invocation is still going (e.g. this run
		// takes longer than the cron interval). Non-blocking: if already locked, just exit.
		$lock = $this->db->query("SELECT GET_LOCK('cron_import_results', 0) as got")->row();
		if (!$lock || $lock->got != 1) {
			echo "Another import run is already in progress. Skipping.\n";
			return;
		}
		register_shutdown_function(function () {
			$this->db->query("SELECT RELEASE_LOCK('cron_import_results')");
		});

		$budget = (int) $this->config->item('cron_import_pages_per_run');
		$throttle = (int) $this->config->item('cron_import_throttle_seconds');

		// Built once, up front, and threaded (by reference) through every step
		// below that resolves a location name — so a location discovered by one
		// step is immediately visible to the next instead of each step querying
		// the whole locations table fresh.
		$location_map = $this->_get_location_map();

		// Whether to actually hit statesavings.ie to check for a new draw this run.
		// Always check if something is already confirmed but not yet fully
		// imported (auto_import=1, published=0) — we need to keep making progress
		// on that regardless of the throttle. Otherwise, only check once
		// LIVE_CHECK_INTERVAL_SECONDS has passed since the last check.
		$do_live_check = ($this->_get_active_draw() !== null) || $this->_live_check_due();

		if ($do_live_check) {
			// The only place *confirmed* draw rows get created for the real site —
			// see the docblock on _seed_placeholder_draw_rows_for_dummy_data() for
			// why we don't guess draw dates ahead of time any more. Gated by
			// $budget like every other request-issuing step below, so a very low
			// configured budget (e.g. 1) can't still cost more requests than it allows.
			if ($budget > 0) {
				$confirmed = $this->_confirm_available_draws($budget);
				if ($confirmed > 0) {
					echo "Confirmed {$confirmed} draw(s) now listed on statesavings.ie — enabled for import.\n";
				}
			}

			if ($budget > 0) {
				$this->_sync_next_draw_preview();
				$budget -= 1;
			}

			$this->_mark_live_check_done();
		} else {
			echo "Skipping live statesavings.ie check — checked recently and nothing is actively importing.\n";
		}

		// Re-fetch: a live check just above may have newly confirmed a draw.
		$draw = $this->_get_active_draw();
		if (!$draw) {
			echo "Nothing to do — all past draws are already imported.\n";
			if ($budget > 0) {
				$this->_repair_missing_locations($budget, $throttle, $location_map);
			}
			return;
		}

		echo "Draw {$draw->draw_date} (id {$draw->id})\n";

		// Discover tiers for this draw if we haven't yet.
		$progress_rows = $this->db->select('*')->from('draw_import_progress')->where('draw_id', $draw->id)->get()->result();
		if (empty($progress_rows)) {
			$html = statesavings_fetch_page($draw->draw_date, 'all', 1);
			$budget--;
			if ($html === null) {
				echo "Request failed while discovering tiers. Will retry next run.\n";
				return;
			}

			$tiers = statesavings_parse_prize_options($html);
			if (empty($tiers)) {
				// No draw was actually held on this date (e.g. skipped week) — remove the placeholder row.
				$this->db->where('id', $draw->id)->delete('draws');
				echo "No draw found for {$draw->draw_date} — removed placeholder, will try the next date on the next run.\n";
				return;
			}

			// is_jackpot was only ever a calendar guess (last Friday of the month) made
			// when the placeholder was created — correct it from the real tiers we just
			// discovered, since a holiday-shifted draw date isn't reliably "the last
			// Friday" even in a jackpot week.
			$is_jackpot = in_array(500000.0, array_map('floatval', $tiers)) ? 1 : 0;
			$this->db->where('id', $draw->id)->update('draws', array('is_jackpot' => $is_jackpot));
			$draw->is_jackpot = $is_jackpot;

			foreach ($tiers as $tier) {
				$this->db->insert('draw_import_progress', array(
					'draw_id' => $draw->id,
					'prize_value' => $tier,
					'next_page' => 1,
					'done' => 0,
				));
			}
			echo "Discovered tiers: " . implode(', ', $tiers) . "\n";

			$progress_rows = $this->db->select('*')->from('draw_import_progress')->where('draw_id', $draw->id)->get()->result();

			if ($budget <= 0) {
				echo "Budget used on discovery. Will fetch pages on the next run.\n";
				return;
			}
		}

		$pages_fetched = (int) $this->config->item('cron_import_pages_per_run') - $budget;
		while ($budget > 0) {
			$tier_row = $this->db->select('*')->from('draw_import_progress')
				->where('draw_id', $draw->id)->where('done', 0)
				->order_by('prize_value', 'desc')->limit(1)->get()->row();

			if (!$tier_row) {
				$this->_complete_draw($draw->id);
				echo "Draw {$draw->draw_date} fully imported and published.\n";
				break;
			}

			$html = statesavings_fetch_page($draw->draw_date, $tier_row->prize_value, $tier_row->next_page);
			$budget--;
			$pages_fetched++;

			if ($html === null) {
				echo "Request failed on tier {$tier_row->prize_value} page {$tier_row->next_page}. Will retry next run.\n";
				break;
			}

			$this->db->trans_start();

			$total_pages = $tier_row->total_pages;
			if ($total_pages === null) {
				$total_count = statesavings_parse_total_count($html);
				$total_pages = max(1, (int) ceil($total_count / 10));
				$this->db->where('id', $tier_row->id)->update('draw_import_progress', array(
					'total_count' => $total_count,
					'total_pages' => $total_pages,
				));
			}

			$rows = statesavings_parse_rows($html);
			$batch = array();
			foreach ($rows as $row) {
				list($bond_number, $location_name) = $row;
				$batch[] = array(
					'draw_id' => $draw->id,
					'bond_number' => strtoupper($bond_number),
					'prize_value' => $tier_row->prize_value,
					'location_id' => $this->_resolve_location_id($location_map, $location_name),
				);
			}
			db_insert_batch_ignore($this->db, 'draw_winners', $batch);

			$next_page = $tier_row->next_page + 1;
			$done = $next_page > $total_pages ? 1 : 0;
			$this->db->where('id', $tier_row->id)->update('draw_import_progress', array(
				'next_page' => $next_page,
				'done' => $done,
			));

			$this->db->trans_complete();

			echo "Tier {$tier_row->prize_value}: page {$tier_row->next_page}/{$total_pages} (" . count($rows) . " rows)" . ($done ? " — tier complete" : "") . "\n";

			if ($budget > 0) {
				sleep($throttle);
			}
		}

		echo "Done. {$pages_fetched} page(s) fetched this run.\n";

		if ($budget > 0) {
			$this->_repair_missing_locations($budget, $throttle, $location_map);
		}
	}

	/**
	 * Dev/demo only — NOT used by the real import path (import_results()).
	 * That path now only ever creates a draw row once _confirm_available_draws()
	 * has actually seen statesavings.ie list it (i.e. the draw has genuinely
	 * happened and results are posted), rather than guessing a weekly-Friday
	 * cadence ahead of time. We tried the guess-then-confirm approach and it
	 * produced wrong guesses whenever a real draw landed on a non-Friday (bank
	 * holidays like Good Friday, and other one-off shifts around Christmas/New
	 * Year that don't even follow a predictable rule) — see git history around
	 * 2026-08-31 for the incident. Pure reactive discovery sidesteps that
	 * entire class of bug: we simply never claim a date is a draw until
	 * statesavings.ie itself confirms it.
	 *
	 * seed_dummy() still needs *some* rows to fabricate fake data onto for
	 * local testing, so it keeps its own calendar-based Friday generator here
	 * — it doesn't matter if this guesses "wrong" since it's fake data anyway.
	 *
	 * Cheap: local DB only, no statesavings.ie requests, doesn't touch budget.
	 */
	private function _seed_placeholder_draw_rows_for_dummy_data()
	{
		$today = date('Y-m-d');

		$d = new DateTime(self::BACKFILL_START_DATE);
		while ($d->format('Y-m-d') <= $today) {
			$this->_insert_draw_placeholder($d->format('Y-m-d'));
			$d->modify('+7 days');
		}
	}

	private function _insert_draw_placeholder($date)
	{
		// auto_import starts at 0 — nothing in the real import path uses these
		// rows (see docblock above), so this is purely to give seed_dummy()
		// something to iterate over.
		$is_jackpot = is_last_friday_of_month($date) ? 1 : 0;
		$this->db->query(
			"INSERT IGNORE INTO draws (draw_date, is_jackpot, published, auto_import, total_prize_fund, total_prizes_count) VALUES (?, ?, 0, 0, 0, 0)",
			array($date, $is_jackpot)
		);
	}

	/**
	 * The sole source of new draw rows for the real site. Used to read
	 * statesavings.ie's "Show winners from" date list off their results page —
	 * but as of the outage investigated 2026-09-04, that page (and their
	 * homepage) sit behind a Radware bot challenge that no plain HTTP request
	 * can pass, regardless of User-Agent, so that listing is no longer
	 * reachable here. Their winners-table API (STATESAVINGS_RESULTS_URL, used
	 * by statesavings_fetch_page() below) is NOT behind that challenge, so we
	 * use it instead: probe calendar dates one by one, starting the day after
	 * the newest date we already have a row for, and treat the first date
	 * whose response reports any winners as the new draw.
	 *
	 * Still deliberately reactive rather than predictive, same as the old
	 * approach: nothing is ever confirmed until the API itself reports real
	 * results for that exact date, so a holiday shift (Good Friday, one-off
	 * shifts around Christmas/New Year — see git history around 2026-08-31)
	 * still can't produce a wrongly-guessed date, it just takes an extra day
	 * or two of probing to reach. MAX_DRAW_SCAN_DAYS bounds that probing, and
	 * _no_draw_confirmed_through()/_mark_no_draw_confirmed_through() cache how
	 * far we've already scanned so a run of empty days isn't re-probed on
	 * every live check.
	 *
	 * Decrements $budget (by reference) by one per date probed — callers must
	 * gate on $budget > 0 before calling, same as every other request-issuing
	 * step. Returns how many draws were newly confirmed (0 or 1 in practice:
	 * the scan stops at the first hit).
	 */
	private function _confirm_available_draws(&$budget)
	{
		// Only counts confirmed (auto_import=1) rows as "already known" — an
		// unconfirmed future preview row from _sync_next_draw_preview() (or a
		// legacy manual entry) must NOT push the scan past its own date, or
		// that date would never get probed/confirmed at all.
		$latest = $this->db->select_max('draw_date', 'max_date')->from('draws')->where('auto_import', 1)->get()->row();
		$scan_from = ($latest && $latest->max_date) ? $latest->max_date : self::BACKFILL_START_DATE;
		$scan_from = max($scan_from, $this->_no_draw_confirmed_through());

		$cursor = new DateTime($scan_from);
		$cursor->modify('+1 day');
		$today = date('Y-m-d');

		$confirmed = 0;
		$days_checked = 0;

		while ($budget > 0 && $days_checked < self::MAX_DRAW_SCAN_DAYS && $cursor->format('Y-m-d') <= $today) {
			$date = $cursor->format('Y-m-d');

			$html = statesavings_fetch_page($date, 'all', 1);
			$budget--;
			$days_checked++;

			if ($html === null) {
				break; // request failed — retry from this date on the next run
			}

			if (statesavings_parse_total_count($html) > 0) {
				$this->_confirm_draw_date($date);
				$confirmed++;
				break; // one confirmed draw is all a single check needs to find
			}

			$this->_mark_no_draw_confirmed_through($date);
			$cursor->modify('+1 day');
		}

		return $confirmed;
	}

	/**
	 * Marks $date as a confirmed real draw: flips an existing row (e.g. a
	 * stale _sync_next_draw_preview() row from before it too went dark behind
	 * the Radware challenge) to auto_import=1, or inserts a new confirmed row
	 * if none exists yet. is_jackpot here is a provisional calendar guess; it
	 * gets corrected from the real prize tiers during tier discovery in
	 * import_results(), since a holiday-shifted date isn't reliably "the last
	 * Friday of the month" even in a jackpot week.
	 */
	private function _confirm_draw_date($date)
	{
		$existing = $this->db->select('id')->from('draws')->where('draw_date', $date)->get()->row();
		if ($existing) {
			$this->db->where('id', $existing->id)->update('draws', array('auto_import' => 1));
		} else {
			$is_jackpot = is_last_friday_of_month($date) ? 1 : 0;
			$this->db->query(
				"INSERT IGNORE INTO draws (draw_date, is_jackpot, published, auto_import, total_prize_fund, total_prizes_count) VALUES (?, ?, 0, 1, 0, 0)",
				array($date, $is_jackpot)
			);
		}
		echo "Confirmed {$date} on statesavings.ie — enabled for import.\n";
	}

	/**
	 * The one deliberate exception to "never guess a draw date" (see
	 * _confirm_available_draws() above): statesavings.ie's main product page
	 * carries a "Next Draw" banner that's driven by their real internal
	 * schedule, not calendar math — confirmed via a Wayback Machine snapshot
	 * from 24 Dec 2024 that correctly showed an irregular Christmas/New Year
	 * date 7 days ahead of time, instead of the naive next-Friday guess. So
	 * this is authoritative, not speculative, and safe to publish one row for.
	 *
	 * Keeps at most one "preview" row in sync with whatever that banner
	 * currently says, rather than committing to it once — if statesavings
	 * revises the date, we follow. Any row matching auto_import=0,
	 * published=0, draw_date > today is safe to manage this way, since under
	 * the current design nothing else ever creates a future unconfirmed row.
	 * Once the date arrives and _confirm_available_draws() sees it in the
	 * real results listing, this row is what gets flipped to auto_import=1
	 * and imported normally — no different from any other confirmed draw.
	 */
	private function _sync_next_draw_preview()
	{
		$html = statesavings_fetch_home_page();
		if ($html === null) {
			return;
		}
		$next_date = statesavings_parse_next_draw_date($html);
		if ($next_date === null) {
			// Page fetched fine but we couldn't find/parse a next-draw date from it —
			// likely a markup change on their end. Worth surfacing in the cron log
			// even though we deliberately leave any existing preview row untouched
			// (a single failed parse shouldn't discard a previously-good date).
			echo "Couldn't read a next-draw date from statesavings.ie's homepage this run — leaving any existing preview as-is.\n";
			return;
		}
		if ($next_date <= date('Y-m-d')) {
			return;
		}

		$existing_preview = $this->db->select('draw_date')->from('draws')
			->where('auto_import', 0)->where('published', 0)->where('draw_date >', date('Y-m-d'))
			->get()->result();

		$already_current = false;
		foreach ($existing_preview as $row) {
			if ($row->draw_date === $next_date) {
				$already_current = true;
			} else {
				// statesavings revised their stated next-draw date since our last check —
				// drop our stale preview so we don't end up with two future rows.
				$this->db->where('draw_date', $row->draw_date)->where('auto_import', 0)->where('published', 0)->delete('draws');
				echo "Next-draw preview changed from {$row->draw_date} to {$next_date} — updated.\n";
			}
		}

		if (!$already_current) {
			$is_jackpot = is_last_friday_of_month($next_date) ? 1 : 0;
			$this->db->query(
				"INSERT IGNORE INTO draws (draw_date, is_jackpot, published, auto_import, total_prize_fund, total_prizes_count) VALUES (?, ?, 0, 0, 0, 0)",
				array($next_date, $is_jackpot)
			);
			echo "Added preview row for the next draw, {$next_date} (per statesavings.ie's own schedule).\n";
		}
	}

	/**
	 * Dev/demo helper: fills every past draw that has no data yet (skips
	 * anything the real import has already touched, i.e. has winners or
	 * progress rows) with plausible fake tiers + winners, so the site can
	 * be seen fully working without waiting for the real backfill.
	 */
	public function seed_dummy()
	{
		header('Content-Type: text/plain');
		set_time_limit(300);

		if ($this->input->get('key') !== $this->config->item('cron_secret')) {
			http_response_code(403);
			echo "Forbidden\n";
			return;
		}

		$lock = $this->db->query("SELECT GET_LOCK('cron_import_results', 0) as got")->row();
		if (!$lock || $lock->got != 1) {
			echo "An import/seed run is already in progress. Skipping.\n";
			return;
		}
		register_shutdown_function(function () {
			$this->db->query("SELECT RELEASE_LOCK('cron_import_results')");
		});

		$this->_seed_placeholder_draw_rows_for_dummy_data();

		// ?force_date=YYYY-MM-DD wipes and re-seeds a specific draw even if it
		// already has real (partial) data — e.g. to fill a draw that's mid
		// real-backfill without waiting for it to finish.
		$force_date = $this->input->get('force_date');
		if ($force_date && preg_match('/^\d{4}-\d{2}-\d{2}$/', $force_date)) {
			$forced = $this->db->select('id')->from('draws')->where('draw_date', $force_date)->get()->row();
			if ($forced) {
				$this->db->where('draw_id', $forced->id)->delete('draw_winners');
				$this->db->where('draw_id', $forced->id)->delete('draw_prize_tiers');
				$this->db->where('draw_id', $forced->id)->delete('draw_import_progress');
				$this->db->where('id', $forced->id)->update('draws', array('published' => 0, 'total_prizes_count' => 0, 'total_prize_fund' => 0));
				echo "Cleared existing data for {$force_date} (forced re-seed).\n";
			}
		}

		$locations = array();
		foreach ($this->db->select('id')->from('locations')->get()->result() as $loc) {
			$locations[] = (int) $loc->id;
		}
		$letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

		$draws = $this->db->select('draws.id, draws.draw_date, draws.is_jackpot')->from('draws')
			->where('draws.draw_date <=', date('Y-m-d'))
			->where('draws.published', 0)
			->where('(SELECT COUNT(*) FROM draw_winners w WHERE w.draw_id = draws.id) = 0', null, false)
			->where('(SELECT COUNT(*) FROM draw_import_progress p WHERE p.draw_id = draws.id) = 0', null, false)
			->order_by('draws.draw_date')
			->get()->result();

		echo "Seeding dummy data for " . count($draws) . " draw(s)...\n";

		foreach ($draws as $draw) {
			$tiers = array();
			if ($draw->is_jackpot) {
				$tiers[] = array('value' => 500000, 'count' => 1);
			}
			$tiers[] = array('value' => 50000, 'count' => 1);
			$tiers[] = array('value' => 1000, 'count' => 20);
			$tiers[] = array('value' => 500, 'count' => 20);
			$tiers[] = array('value' => 75, 'count' => mt_rand(120, 200));

			$this->db->trans_start();

			$sort_order = 1;
			foreach ($tiers as $tier) {
				$this->db->insert('draw_prize_tiers', array(
					'draw_id' => $draw->id,
					'prize_value' => $tier['value'],
					'prize_count' => $tier['count'],
					'sort_order' => $sort_order++,
				));
			}

			$total_prizes = 0;
			$total_fund = 0;
			$batch = array();
			foreach ($tiers as $tier) {
				for ($i = 0; $i < $tier['count']; $i++) {
					$prefix_len = mt_rand(2, 3);
					$prefix = '';
					for ($j = 0; $j < $prefix_len; $j++) {
						$prefix .= $letters[mt_rand(0, 25)];
					}
					$bond_number = $prefix . str_pad((string) mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

					$batch[] = array(
						'draw_id' => $draw->id,
						'bond_number' => $bond_number,
						'prize_value' => $tier['value'],
						'location_id' => $locations[array_rand($locations)],
					);
					$total_prizes++;
					$total_fund += $tier['value'];
				}
			}
			db_insert_batch_ignore($this->db, 'draw_winners', $batch);

			// Recompute from what actually landed, in case a random bond-number
			// collision got silently ignored by the unique constraint.
			$actual = $this->db->select('COUNT(*) as cnt, SUM(prize_value) as total')->from('draw_winners')->where('draw_id', $draw->id)->get()->row();
			$this->db->where('id', $draw->id)->update('draws', array(
				'total_prizes_count' => $actual->cnt,
				'total_prize_fund' => $actual->total,
				'published' => 1,
			));

			$this->db->trans_complete();

			echo "  {$draw->draw_date}: {$total_prizes} winners, €" . number_format($total_fund, 2) . "\n";
		}

		echo "Done.\n";
	}

	private function _get_active_draw()
	{
		return $this->db->select('*')->from('draws')
			->where('auto_import', 1)->where('published', 0)
			->where('draw_date <=', date('Y-m-d'))
			->order_by('draw_date', 'asc')->limit(1)->get()->row();
	}

	/**
	 * True if it's been at least LIVE_CHECK_INTERVAL_SECONDS since the last
	 * live statesavings.ie check (or we've never recorded one). Defensively
	 * returns true — i.e. "always check" — if the cron_state table doesn't
	 * exist yet (e.g. this code deployed before the migration ran), so a
	 * missed schema update never silently stalls the whole import pipeline;
	 * it just means the throttle doesn't kick in until the table exists.
	 */
	private function _live_check_due()
	{
		if (!$this->db->table_exists('cron_state')) {
			return true;
		}
		// The whole "how long ago" comparison happens inside MySQL, using its own
		// NOW() on both the stored value and the comparison — deliberately never
		// mixes it with PHP's time()/date(), since the two can (and here, do) run
		// in different timezones (this environment: PHP is UTC, MySQL server is
		// UTC+5) — comparing across that gap would silently throttle wrong by
		// however many hours the two clocks disagree by.
		$row = $this->db->query(
			"SELECT (value IS NULL OR value < NOW() - INTERVAL " . self::LIVE_CHECK_INTERVAL_SECONDS . " SECOND) as due
			 FROM cron_state WHERE name = 'last_live_check'"
		)->row();
		if (!$row) {
			return true; // no row yet at all — never checked
		}
		return (bool) $row->due;
	}

	private function _mark_live_check_done()
	{
		if (!$this->db->table_exists('cron_state')) {
			return;
		}
		$this->db->query(
			"INSERT INTO cron_state (name, value) VALUES ('last_live_check', NOW()) ON DUPLICATE KEY UPDATE value = NOW()"
		);
	}

	/**
	 * How far _confirm_available_draws() has already scanned with no draw
	 * found, so a run of empty days (the usual Sat-Thu between Fridays) isn't
	 * re-probed via the API on every 30-minute live check. Falls back to
	 * BACKFILL_START_DATE if the cron_state table doesn't exist yet or has no
	 * row — same defensive default as _live_check_due() above.
	 */
	private function _no_draw_confirmed_through()
	{
		if (!$this->db->table_exists('cron_state')) {
			return self::BACKFILL_START_DATE;
		}
		$row = $this->db->select('value')->from('cron_state')->where('name', 'no_draw_confirmed_through')->get()->row();
		return $row ? $row->value : self::BACKFILL_START_DATE;
	}

	private function _mark_no_draw_confirmed_through($date)
	{
		if (!$this->db->table_exists('cron_state')) {
			return;
		}
		$this->db->query(
			"INSERT INTO cron_state (name, value) VALUES ('no_draw_confirmed_through', ?) ON DUPLICATE KEY UPDATE value = ?",
			array($date, $date)
		);
	}

	private function _get_location_map()
	{
		$locations = $this->db->select('id, name')->from('locations')->get()->result();
		$map = array();
		foreach ($locations as $loc) {
			$map[strtolower(trim($loc->name))] = $loc->id;
		}
		return $map;
	}

	/**
	 * Looks up a location by exact name (case-insensitively via the map built
	 * in _get_location_map(), but this is the fallback for a name that map
	 * doesn't have yet). `name` is UNIQUE, so INSERT IGNORE is safe against a
	 * race with another cron run and against re-discovering the same new
	 * location twice within one run.
	 */
	private function _get_or_create_location_id($name)
	{
		$this->db->query("INSERT IGNORE INTO locations (name) VALUES (?)", array($name));
		$row = $this->db->select('id')->from('locations')->where('name', $name)->get()->row();
		return $row ? $row->id : null;
	}

	/**
	 * Resolves a location name to an id via $location_map (from
	 * _get_location_map()), creating it if we haven't seen it before.
	 * Statesavings.ie reports overseas winners by their actual country/place
	 * (e.g. "U.K", "Australia", "Isle of man") rather than a single generic
	 * "Outside Ireland" bucket, so any name we haven't seen yet is a genuine
	 * new location, not bad data — capture it instead of losing it to NULL.
	 *
	 * Shared by the main import loop and _repair_missing_locations() so
	 * location-name normalization only has one implementation to keep in
	 * sync. Uses array_key_exists() rather than isset(): a name that failed
	 * to resolve gets cached as `null`, and isset() treats a null value as
	 * "not set" — which would otherwise re-attempt the INSERT+SELECT for
	 * that same unresolvable name on every subsequent row that shares it.
	 */
	private function _resolve_location_id(&$location_map, $location_name)
	{
		$location_name = trim($location_name);
		$key = strtolower($location_name);
		if ($key === '') {
			return null;
		}
		if (!array_key_exists($key, $location_map)) {
			$location_map[$key] = $this->_get_or_create_location_id($location_name);
		}
		return $location_map[$key];
	}

	/**
	 * Backfills draw_winners.location_id for rows imported before we started
	 * capturing overseas location names (see _get_or_create_location_id()) —
	 * those rows were left NULL. Re-fetches statesavings.ie's own "global"
	 * filter (their bucket for every non-Irish-county winner) per affected
	 * draw/prize-tier, matches rows back onto our existing winners by bond
	 * number, and fills in location_id. Runs with its own small slice of the
	 * cron's page budget so it catches up gradually in the background rather
	 * than needing a one-off manual trigger; naturally idempotent (re-running
	 * only ever touches rows still NULL) so a partial run is safe to resume.
	 */
	private function _repair_missing_locations($budget, $throttle, &$location_map)
	{
		// GROUP BY + COUNT up front, once, rather than a fresh COUNT(*) query before
		// every page fetch inside the loop below — $remaining is then tracked purely
		// in memory, decremented by rows actually fixed each page.
		$pending = $this->db->query(
			"SELECT draw_id, prize_value, COUNT(*) as remaining FROM draw_winners WHERE location_id IS NULL GROUP BY draw_id, prize_value ORDER BY draw_id DESC, prize_value DESC"
		)->result();
		if (empty($pending)) {
			return;
		}

		$draw_dates = array();
		foreach ($this->db->select('id, draw_date')->from('draws')->get()->result() as $d) {
			$draw_dates[$d->id] = $d->draw_date;
		}

		$fixed = 0;

		foreach ($pending as $pair) {
			if ($budget <= 0) {
				break;
			}
			if (!isset($draw_dates[$pair->draw_id])) {
				continue;
			}

			$remaining = (int) $pair->remaining;

			// MAX_REPAIR_PAGES_PER_PAIR bounds how much of one run's budget a single
			// pair can consume — a pair with even one permanently-unresolvable row
			// (e.g. a genuinely blank source location) would otherwise never let
			// $remaining reach 0, starving every other pending pair every run.
			for ($page = 0; $budget > 0 && $remaining > 0 && $page < self::MAX_REPAIR_PAGES_PER_PAIR; $page++) {
				$html = statesavings_http_get(STATESAVINGS_RESULTS_URL . '?' . http_build_query(array(
					'drawDate' => $draw_dates[$pair->draw_id],
					'page' => $page,
					'prizeValue' => $pair->prize_value,
					'location' => 'global',
					'sortBy' => 'prizevaluedesc',
					'search' => '',
				)));
				$budget--;
				if ($html === null) {
					break;
				}

				$rows = statesavings_parse_rows($html);
				if (empty($rows)) {
					break; // past the last page of the "global" bucket for this tier
				}

				foreach ($rows as $row) {
					list($bond_number, $location_name) = $row;
					$location_id = $this->_resolve_location_id($location_map, $location_name);
					if ($location_id === null) {
						continue;
					}
					$this->db->where('draw_id', $pair->draw_id)
						->where('bond_number', strtoupper($bond_number))
						->where('prize_value', $pair->prize_value)
						->where('location_id IS NULL', null, false)
						->update('draw_winners', array('location_id' => $location_id));
					if ($this->db->affected_rows() > 0) {
						$fixed++;
						$remaining--;
					}
				}

				if ($budget > 0 && $remaining > 0) {
					sleep($throttle);
				}
			}
		}

		if ($fixed > 0) {
			echo "Location repair: filled in {$fixed} previously-blank location(s) this run.\n";
		}
	}

	private function _complete_draw($draw_id)
	{
		$totals = $this->db->select('COUNT(*) as cnt, SUM(prize_value) as total')->from('draw_winners')->where('draw_id', $draw_id)->get()->row();
		$this->db->where('id', $draw_id)->update('draws', array(
			'total_prizes_count' => $totals->cnt,
			'total_prize_fund' => $totals->total,
			'published' => 1,
		));
	}

}
