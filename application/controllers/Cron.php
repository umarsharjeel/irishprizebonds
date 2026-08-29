<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

	const BACKFILL_START_DATE = '2026-02-27';

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

		$this->_ensure_draw_rows_exist();

		$budget = (int) $this->config->item('cron_import_pages_per_run');
		$throttle = (int) $this->config->item('cron_import_throttle_seconds');

		$confirmed = $this->_confirm_available_draws();
		$budget -= 1; // that confirmation check costs one request
		if ($confirmed > 0) {
			echo "Confirmed {$confirmed} draw(s) now listed on statesavings.ie — enabled for import.\n";
		}

		$draw = $this->_get_active_draw();
		if (!$draw) {
			echo "Nothing to do — all past draws are already imported.\n";
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

		$location_map = $this->_get_location_map();

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
				$key = strtolower(trim($location_name));
				$batch[] = array(
					'draw_id' => $draw->id,
					'bond_number' => strtoupper($bond_number),
					'prize_value' => $tier_row->prize_value,
					'location_id' => isset($location_map[$key]) ? $location_map[$key] : null,
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
	}

	/**
	 * Keeps the draws table gap-free: every Friday from BACKFILL_START_DATE
	 * through today (so the winner-import loop always has a row to work
	 * with), plus at least MIN_FUTURE_DRAWS Fridays beyond today (so the
	 * site has stable, linkable permalinks for upcoming draws ahead of time
	 * — good for SEO, and they get filled in with real data once the date
	 * passes and the import loop reaches them).
	 *
	 * Cheap: local DB only, no statesavings.ie requests, doesn't touch budget.
	 */
	const MIN_FUTURE_DRAWS = 8;

	private function _ensure_draw_rows_exist()
	{
		$today = date('Y-m-d');

		$d = new DateTime(self::BACKFILL_START_DATE);
		while ($d->format('Y-m-d') <= $today) {
			$this->_insert_draw_placeholder($d->format('Y-m-d'));
			$d->modify('+7 days');
		}

		// $d is now the first Friday strictly after today.
		for ($i = 0; $i < self::MIN_FUTURE_DRAWS; $i++) {
			$this->_insert_draw_placeholder($d->format('Y-m-d'));
			$d->modify('+7 days');
		}
	}

	private function _insert_draw_placeholder($date)
	{
		// auto_import starts at 0: this date is just our calendar guess (weekly Friday).
		// It only becomes eligible for real import once _confirm_available_draws() sees
		// statesavings.ie actually list it — see that method for why.
		$is_jackpot = $this->_is_last_friday_of_month($date) ? 1 : 0;
		$this->db->query(
			"INSERT IGNORE INTO draws (draw_date, is_jackpot, published, auto_import, total_prize_fund, total_prizes_count) VALUES (?, ?, 0, 0, 0, 0)",
			array($date, $is_jackpot)
		);
	}

	/**
	 * Checks statesavings.ie's own "Show winners from" date list and flips
	 * auto_import on for any local unconfirmed draw whose date now appears
	 * there. This is what actually gates a draw for real importing — not
	 * just draw_date <= today — because a date can be in the past without
	 * results being posted yet (results seem to lag a little), and blindly
	 * attempting tier-discovery on a not-yet-posted date would look
	 * indistinguishable from "no draw was held" and get the placeholder
	 * deleted incorrectly.
	 *
	 * Returns how many draws were newly confirmed.
	 */
	private function _confirm_available_draws()
	{
		$html = statesavings_fetch_results_page();
		if ($html === null) {
			return 0;
		}

		if (!preg_match('/<select id="draw-date-select"[^>]*>(.*?)<\/select>/s', $html, $select_match)) {
			return 0;
		}
		preg_match_all('/value="(\d{4}-\d{2}-\d{2})"/', $select_match[1], $m);
		$available_dates = $m[1];
		if (empty($available_dates)) {
			return 0;
		}

		$this->db->where_in('draw_date', $available_dates)
			->where('auto_import', 0)
			->where('published', 0)
			->set('auto_import', 1)
			->update('draws');

		return $this->db->affected_rows();
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

		$this->_ensure_draw_rows_exist();

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

	private function _is_last_friday_of_month($date_str)
	{
		$d = new DateTime($date_str);
		$next = clone $d;
		$next->modify('+7 days');
		return $d->format('n') !== $next->format('n');
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
