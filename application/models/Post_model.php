<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Write-ups ("posts") built from our own draw data.
 *
 * Currently one type: a jackpot article for a monthly €500,000 jackpot draw.
 * Every sentence in a generated article comes from tables we already hold (the
 * draw, its prize tiers, its winners and the earlier jackpot draws), so nothing
 * is invented: no names, no quotes, only what the official results list (a bond
 * number and the county/country it is associated with).
 *
 * All public-facing methods are safe to call before the `posts` table has been
 * created (they return empty/null), so deploying the code ahead of the SQL
 * can't break any page.
 */
class Post_model extends CI_Model {

	const JACKPOT_PRIZE = 500000;
	const TYPE_JACKPOT = 'jackpot';

	private static $table_ok = null;

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('general_helper');
	}

	/** The SQL that creates the table (also in database/2026-11-posts.sql). */
	public static function create_sql()
	{
		return "CREATE TABLE IF NOT EXISTS `posts` (\n"
			. "  `id` int unsigned NOT NULL AUTO_INCREMENT,\n"
			. "  `post_type` varchar(20) NOT NULL DEFAULT 'jackpot',\n"
			. "  `draw_id` int unsigned DEFAULT NULL,\n"
			. "  `slug` varchar(120) NOT NULL,\n"
			. "  `title` varchar(200) NOT NULL,\n"
			. "  `summary` varchar(255) NOT NULL DEFAULT '',\n"
			. "  `body` mediumtext NOT NULL,\n"
			. "  `editor_note` text,\n"
			. "  `status` varchar(12) NOT NULL DEFAULT 'published',\n"
			. "  `published_at` datetime NOT NULL,\n"
			. "  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,\n"
			. "  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n"
			. "  PRIMARY KEY (`id`),\n"
			. "  UNIQUE KEY `slug` (`slug`),\n"
			. "  UNIQUE KEY `type_draw` (`post_type`,`draw_id`),\n"
			. "  KEY `status_published` (`status`,`published_at`)\n"
			. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
	}

	public function table_exists()
	{
		if (self::$table_ok === null) {
			self::$table_ok = (bool) $this->db->table_exists('posts');
		}
		return self::$table_ok;
	}

	// ------------------------------------------------------------------
	// Reading posts (public side)
	// ------------------------------------------------------------------

	public function list_published($limit = 60)
	{
		if (!$this->table_exists()) {
			return array();
		}
		return $this->db->from('posts')->where('status', 'published')
			->order_by('published_at', 'desc')->limit((int) $limit)->get()->result();
	}

	public function find_published_by_slug($slug)
	{
		if (!$this->table_exists() || !preg_match('/^[a-z0-9-]{1,120}$/', (string) $slug)) {
			return null;
		}
		$row = $this->db->from('posts')->where('slug', $slug)->where('status', 'published')->limit(1)->get()->row();
		return $row ? $row : null;
	}

	/** The published article for a draw (id, slug, title), or null. */
	public function published_for_draw($draw_id)
	{
		if (!$this->table_exists()) {
			return null;
		}
		$row = $this->db->select('id, slug, title')->from('posts')
			->where('post_type', self::TYPE_JACKPOT)->where('draw_id', (int) $draw_id)->where('status', 'published')
			->limit(1)->get()->row();
		return $row ? $row : null;
	}

	/** Older / newer published article around $post (by publish time). */
	public function neighbours($post)
	{
		$out = array('older' => null, 'newer' => null);
		if (!$this->table_exists()) {
			return $out;
		}
		$older = $this->db->select('slug, title')->from('posts')->where('status', 'published')
			->where('published_at <', $post->published_at)->order_by('published_at', 'desc')->limit(1)->get()->row();
		$newer = $this->db->select('slug, title')->from('posts')->where('status', 'published')
			->where('published_at >', $post->published_at)->order_by('published_at', 'asc')->limit(1)->get()->row();
		$out['older'] = $older ? $older : null;
		$out['newer'] = $newer ? $newer : null;
		return $out;
	}

	// ------------------------------------------------------------------
	// Publishing (dashboard side)
	// ------------------------------------------------------------------

	/** Existing article (any status) for a draw, or null. */
	public function find_for_draw($draw_id)
	{
		if (!$this->table_exists()) {
			return null;
		}
		$row = $this->db->select('id, slug, title, status, published_at')->from('posts')
			->where('post_type', self::TYPE_JACKPOT)->where('draw_id', (int) $draw_id)->limit(1)->get()->row();
		return $row ? $row : null;
	}

	/**
	 * Every published jackpot draw, newest first, with what the article needs
	 * (is exactly one €500,000 winner recorded, and does it have a location?)
	 * and whether an article already exists for it.
	 */
	public function jackpot_overview()
	{
		$rows = $this->db->query(
			"SELECT d.id, d.draw_date, d.total_prize_fund, d.total_prizes_count,
				(SELECT COUNT(*) FROM draw_winners w WHERE w.draw_id = d.id AND w.prize_value >= ?) AS jackpot_rows,
				(SELECT COUNT(*) FROM draw_winners w WHERE w.draw_id = d.id AND w.prize_value >= ? AND w.location_id IS NOT NULL) AS jackpot_located
			 FROM draws d WHERE d.published = 1 AND d.is_jackpot = 1 ORDER BY d.draw_date DESC",
			array(self::JACKPOT_PRIZE, self::JACKPOT_PRIZE)
		)->result();

		$posts = array();
		if ($this->table_exists()) {
			foreach ($this->db->select('id, draw_id, slug, status')->from('posts')
				->where('post_type', self::TYPE_JACKPOT)->where('draw_id IS NOT NULL', null, false)->get()->result() as $p) {
				$posts[(int) $p->draw_id] = $p;
			}
		}
		foreach ($rows as $r) {
			$r->post = isset($posts[(int) $r->id]) ? $posts[(int) $r->id] : null;
			$r->ready = ((int) $r->jackpot_rows === 1 && (int) $r->jackpot_located === 1);
			if ((int) $r->jackpot_rows === 0) {
				$r->not_ready_reason = 'The €500,000 winner has not been imported for this draw yet.';
			} elseif ((int) $r->jackpot_rows > 1) {
				$r->not_ready_reason = 'More than one €500,000 winner is recorded for this draw, which this article format does not cover.';
			} elseif ((int) $r->jackpot_located === 0) {
				$r->not_ready_reason = 'The jackpot winner has no location yet (locations are imported shortly after the draw).';
			} else {
				$r->not_ready_reason = '';
			}
		}
		return $rows;
	}

	/**
	 * Publishes the article for a jackpot draw unless one already exists.
	 * Returns array(ok, already, error, post_id, slug).
	 */
	public function publish_jackpot($draw_id)
	{
		if (!$this->table_exists()) {
			return array('ok' => false, 'already' => false, 'error' => 'The posts table does not exist yet. Run the SQL shown below first.');
		}
		$existing = $this->find_for_draw($draw_id);
		if ($existing) {
			return array('ok' => false, 'already' => true, 'error' => '', 'post_id' => (int) $existing->id, 'slug' => $existing->slug);
		}

		$gen = $this->generate_jackpot($draw_id);
		if (!$gen['ok']) {
			return array('ok' => false, 'already' => false, 'error' => $gen['error']);
		}
		$p = $gen['post'];

		// A slug clash can only happen if someone created a post by hand with the same slug.
		$slug = $p['slug'];
		$n = 2;
		while ($this->db->from('posts')->where('slug', $slug)->count_all_results() > 0 && $n < 10) {
			$slug = $p['slug'] . '-' . $n++;
		}

		$debug = $this->db->db_debug;
		$this->db->db_debug = false;
		$ok = $this->db->insert('posts', array(
			'post_type' => self::TYPE_JACKPOT,
			'draw_id' => (int) $draw_id,
			'slug' => $slug,
			'title' => $p['title'],
			'summary' => $p['summary'],
			'body' => $p['body'],
			'status' => 'published',
			'published_at' => date('Y-m-d H:i:s'),
		));
		$this->db->db_debug = $debug;

		if (!$ok) {
			// The (post_type, draw_id) unique key stops two clicks racing into two articles.
			$existing = $this->find_for_draw($draw_id);
			if ($existing) {
				return array('ok' => false, 'already' => true, 'error' => '', 'post_id' => (int) $existing->id, 'slug' => $existing->slug);
			}
			return array('ok' => false, 'already' => false, 'error' => 'The article could not be saved.');
		}
		return array('ok' => true, 'already' => false, 'error' => '', 'post_id' => (int) $this->db->insert_id(), 'slug' => $slug);
	}

	// ------------------------------------------------------------------
	// Article generation
	// ------------------------------------------------------------------

	/**
	 * Builds (without saving) the article for a jackpot draw.
	 * Returns array(ok, error, post => array(title, slug, summary, body)).
	 */
	public function generate_jackpot($draw_id)
	{
		$fail = function ($msg) { return array('ok' => false, 'error' => $msg); };

		$draw = $this->db->from('draws')->where('id', (int) $draw_id)->get()->row();
		if (!$draw) {
			return $fail('That draw does not exist.');
		}
		if (!$draw->published) {
			return $fail('That draw has not been published yet.');
		}
		if (!$draw->is_jackpot) {
			return $fail('That is not a jackpot draw.');
		}

		$winners = $this->db->select('draw_winners.bond_number, draw_winners.prize_value, locations.name AS location')
			->from('draw_winners')->join('locations', 'locations.id = draw_winners.location_id', 'left')
			->where('draw_winners.draw_id', $draw->id)->where('draw_winners.prize_value >=', self::JACKPOT_PRIZE)
			->get()->result();
		if (count($winners) === 0) {
			return $fail('The €500,000 winner has not been imported for this draw yet, so there is nothing to write about.');
		}
		if (count($winners) > 1) {
			return $fail('More than one €500,000 winner is recorded for this draw, which this article format does not cover.');
		}
		$w = $winners[0];
		if ($w->location === null || trim($w->location) === '') {
			return $fail('The jackpot winner has no location yet. Locations are imported shortly after the draw, so try again later.');
		}

		$tiers = get_draw_tiers($this->db, $draw->id);
		usort($tiers, function ($a, $b) { return $b->prize_value <=> $a->prize_value; });

		$e = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
		$eur = function ($n) { return '&euro;' . number_format($n); };
		$list = function ($items) {
			$items = array_values($items);
			$n = count($items);
			if ($n <= 1) { return $n ? $items[0] : ''; }
			return implode(', ', array_slice($items, 0, $n - 1)) . ' and ' . $items[$n - 1];
		};

		$date_long = date('j F Y', strtotime($draw->draw_date));
		$date_day = date('l j F Y', strtotime($draw->draw_date));
		$ym = date('Y-m', strtotime($draw->draw_date));
		$month_label = date('F Y', strtotime($draw->draw_date));
		$county = $w->location;
		$bond = $w->bond_number;
		$prize = (float) $w->prize_value;

		$h = array();

		// --- Lead -----------------------------------------------------
		$h[] = '<p class="lead-paragraph">The ' . $eur($prize) . ' jackpot in the Irish Prize Bond draw on ' . $date_day
			. ' went to bond number <strong>' . $e($bond) . '</strong>, which the official results list under <strong>' . $e($county) . '</strong>.</p>';

		// --- The draw in numbers --------------------------------------
		$h[] = '<h2>The draw in numbers</h2>';
		$p = 'The draw awarded ' . number_format($draw->total_prizes_count) . ' prizes with a total prize fund of ' . $eur($draw->total_prize_fund)
			. ', which includes the extra ' . $eur(self::JACKPOT_PRIZE) . ' jackpot.';

		// Set below only when every winner of the top weekly prize is in our data and one of them
		// is in the jackpot county (used by the "Worth noting" section).
		$weekly_fact = null;
		$weekly_top = null;
		foreach ($tiers as $t) {
			if ((float) $t->prize_value < self::JACKPOT_PRIZE) {
				$weekly_top = $t;
				break;
			}
		}
		if ($weekly_top) {
			$cnt = (int) $weekly_top->prize_count;
			$rows = $this->db->select('locations.name AS name, COUNT(*) AS n', false)
				->from('draw_winners')->join('locations', 'locations.id = draw_winners.location_id', 'left')
				->where('draw_winners.draw_id', $draw->id)->where('draw_winners.prize_value', $weekly_top->prize_value)
				->group_by('draw_winners.location_id')->order_by('n', 'desc')->order_by('locations.name', 'asc')
				->get()->result();
			$total_rows = 0;
			$all_located = true;
			$names = array();
			$county_hits = 0;
			foreach ($rows as $r) {
				$total_rows += (int) $r->n;
				if ($r->name === null) {
					$all_located = false;
				} else {
					$names[] = $e($r->name) . ((int) $r->n > 1 ? ' (' . (int) $r->n . ')' : '');
					if (strcasecmp($r->name, $county) === 0) {
						$county_hits = (int) $r->n;
					}
				}
			}
			$p .= ' Besides the jackpot, the top weekly prize of ' . $eur($weekly_top->prize_value) . ' went to '
				. ($cnt === 1 ? 'a single bond' : number_format($cnt) . ' bonds');
			// Only name the counties when every one of those winners is in our data, so the list can't be partial.
			if ($total_rows === $cnt && $all_located && !empty($names)) {
				$p .= ', listed under ' . $list($names);
				if ($county_hits > 0) {
					$weekly_fact = array('value' => $weekly_top->prize_value, 'cnt' => $cnt, 'hits' => $county_hits);
				}
			}
			$p .= '.';
		}

		$big = 0;
		foreach ($tiers as $t) {
			if ((float) $t->prize_value >= 1000) {
				$big += (int) $t->prize_count;
			}
		}
		if ($big > 0) {
			$p .= ' In all, ' . number_format($big) . ' ' . ($big === 1 ? 'prize was' : 'prizes were') . ' worth ' . $eur(1000) . ' or more.';
		}
		$h[] = '<p>' . $p . '</p>';

		$shown = array();
		foreach ($tiers as $t) {
			if ((float) $t->prize_value >= 1000) {
				$shown[] = $t;
			}
		}
		if (!empty($shown)) {
			$shown = array_slice($shown, 0, 8);
			$tr = '';
			foreach ($shown as $t) {
				$is_j = ((float) $t->prize_value >= self::JACKPOT_PRIZE);
				$tr .= '<tr><td>' . $eur($t->prize_value) . ($is_j ? ' (jackpot)' : '') . '</td><td>' . number_format($t->prize_count) . '</td></tr>';
			}
			$h[] = '<div class="table-wrap" style="max-width:420px;"><table class="data-table data-table-compact"><thead><tr><th>Prize</th><th>Number awarded</th></tr></thead><tbody>'
				. $tr . '</tbody></table></div>';
		}

		// --- Jackpot winners in our archive ---------------------------
		$hist = $this->db->query(
			"SELECT d.draw_date, w.bond_number, l.name AS location
			 FROM draws d
			 JOIN draw_winners w ON w.draw_id = d.id AND w.prize_value >= ?
			 LEFT JOIN locations l ON l.id = w.location_id
			 WHERE d.published = 1 AND d.is_jackpot = 1 AND d.draw_date <= ?
			 ORDER BY d.draw_date DESC",
			array(self::JACKPOT_PRIZE, $draw->draw_date)
		)->result();
		$jackpot_draws = (int) $this->db->from('draws')->where('published', 1)->where('is_jackpot', 1)
			->where('draw_date <=', $draw->draw_date)->count_all_results();
		$first = $this->db->select_min('draw_date')->from('draws')->where('published', 1)->where('is_jackpot', 1)
			->where('draw_date <=', $draw->draw_date)->get()->row();

		$m = count($hist);
		if ($m >= 2) {
			$h[] = '<h2>Jackpot winners in our archive</h2>';
			$k = 0;
			foreach ($hist as $r) {
				if ($r->location !== null && strcasecmp($r->location, $county) === 0) {
					$k++;
				}
			}
			$p = 'Our archive holds ' . $jackpot_draws . ' jackpot draws up to and including this one, the earliest on '
				. date('j F Y', strtotime($first->draw_date)) . '. ';
			if ($k <= 1) {
				$p .= 'This is the first jackpot win listed under ' . $e($county) . ' in the draws we hold.';
			} else {
				$p .= $e($county) . ' has now had ' . $k . ' of the ' . $m . ' jackpot wins listed below.';
			}
			$prev = $hist[1];
			$p .= ' The previous jackpot, on ' . date('j F Y', strtotime($prev->draw_date)) . ', went to bond ' . $e($prev->bond_number)
				. ($prev->location !== null ? ', listed under ' . $e($prev->location) : '') . '.';
			$h[] = '<p>' . $p . '</p>';

			$tr = '';
			foreach (array_slice($hist, 0, 12) as $i => $r) {
				$cells = '<td>' . date('d M Y', strtotime($r->draw_date)) . '</td><td>' . $e($r->bond_number) . '</td><td>' . ($r->location !== null ? $e($r->location) : '&ndash;') . '</td>';
				$tr .= '<tr>' . ($i === 0 ? str_replace(array('<td>', '</td>'), array('<td><strong>', '</strong></td>'), $cells) : $cells) . '</tr>';
			}
			$h[] = '<div class="table-wrap" style="max-width:520px;"><table class="data-table data-table-compact"><thead><tr><th>Draw</th><th>Bond</th><th>Location</th></tr></thead><tbody>'
				. $tr . '</tbody></table></div>';
			if ($m > 12) {
				$h[] = '<p class="text-muted">The 12 most recent jackpots are shown.</p>';
			}
		} else {
			$h[] = '<h2>Jackpot winners in our archive</h2>';
			$h[] = '<p>This is the earliest jackpot draw we hold, so there are no earlier jackpot winners in our archive to compare it with.</p>';
		}

		// --- Compared with the previous jackpot draw (like-for-like only) ---
		$new_era = ($draw->draw_date >= PRIZE_CHANGE_DATE);
		$this->db->select('draw_date, total_prize_fund, total_prizes_count')->from('draws')
			->where('published', 1)->where('is_jackpot', 1)->where('total_prize_fund >', 0)->where('draw_date <', $draw->draw_date);
		$this->db->where($new_era ? 'draw_date >=' : 'draw_date <', PRIZE_CHANGE_DATE);
		$cmp = $this->db->order_by('draw_date', 'desc')->limit(1)->get()->row();

		if ($cmp) {
			$diff = (float) $draw->total_prize_fund - (float) $cmp->total_prize_fund;
			$pd = (int) $draw->total_prizes_count - (int) $cmp->total_prizes_count;
			$p = 'The previous jackpot draw, on ' . date('j F Y', strtotime($cmp->draw_date)) . ', had a total prize fund of '
				. $eur($cmp->total_prize_fund) . ' across ' . number_format($cmp->total_prizes_count) . ' prizes. ';
			if (abs($diff) < 0.5) {
				$p .= 'This draw\'s fund was the same';
			} else {
				$p .= 'This draw\'s fund was ' . $eur(abs($diff)) . ($diff > 0 ? ' higher' : ' lower');
			}
			if ($pd === 0) {
				$p .= ', and it awarded the same number of prizes.';
			} else {
				$p .= ', and it awarded ' . number_format(abs($pd)) . ($pd > 0 ? ' more' : ' fewer') . ' prizes.';
			}
			$h[] = '<h2>Compared with the previous jackpot draw</h2>';
			$h[] = '<p>' . $p . '</p>';
		} elseif ($new_era) {
			$earlier = (int) $this->db->from('draws')->where('published', 1)->where('is_jackpot', 1)
				->where('draw_date <', PRIZE_CHANGE_DATE)->count_all_results();
			if ($earlier > 0) {
				$h[] = '<h2>Compared with the previous jackpot draw</h2>';
				$h[] = '<p>The Prize Bond fund and prize tiers were increased from 1 September 2026, so earlier jackpot draws can\'t be compared directly with this one. See <a href="/stats/prize-fund/">Prize Fund Trends</a> for the before-and-after.</p>';
			}
		}

		// --- Worth noting ---------------------------------------------
		// Short facts that are only included when they are actually true for this draw, so the
		// section (and the article) differs from month to month because the data does. Each one
		// is worked out from our own tables and is left out when there isn't enough to say it.
		$facts = array();

		// 1. The jackpot county also took the draw's top weekly prize.
		if ($weekly_fact) {
			$wv = $eur($weekly_fact['value']);
			if ($weekly_fact['cnt'] === 1) {
				$facts[] = $e($county) . ' also won the draw\'s top weekly prize of ' . $wv . ', so the county took both of the biggest prizes in this draw.';
			} else {
				$of = ($weekly_fact['hits'] === $weekly_fact['cnt']) ? 'all ' . $weekly_fact['cnt']
					: ($weekly_fact['hits'] === 1 ? 'one of the ' . $weekly_fact['cnt'] : $weekly_fact['hits'] . ' of the ' . $weekly_fact['cnt']);
				$facts[] = $e($county) . ' also won ' . $of . ' top weekly prizes of ' . $wv . ' in this draw.';
			}
		}

		// 2. Back-to-back wins, or how long since this county's previous jackpot.
		for ($i = 1; $i < $m; $i++) {
			if ($hist[$i]->location !== null && strcasecmp($hist[$i]->location, $county) === 0) {
				$when = date('j F Y', strtotime($hist[$i]->draw_date));
				$facts[] = ($i === 1)
					? $e($county) . ' also won the previous jackpot, on ' . $when . ', so the county has now won back-to-back jackpots.'
					: 'The previous jackpot win listed under ' . $e($county) . ' was on ' . $when . ', ' . $i . ' jackpot draws earlier.';
				break;
			}
		}

		// 3 + 4. Highest / lowest prize fund and prize count among comparable jackpot draws
		// (same side of the prize increase, up to and including this draw; only when no other draw ties).
		$rk = $this->db->query(
			"SELECT COUNT(*) AS n,
			        SUM(total_prize_fund > ?) AS fund_above, SUM(total_prize_fund < ?) AS fund_below, SUM(total_prize_fund = ?) AS fund_equal,
			        SUM(total_prizes_count > ?) AS pr_above, SUM(total_prizes_count < ?) AS pr_below, SUM(total_prizes_count = ?) AS pr_equal
			 FROM draws WHERE published = 1 AND is_jackpot = 1 AND total_prize_fund > 0 AND draw_date <= ? AND draw_date " . ($new_era ? '>=' : '<') . ' ?',
			array($draw->total_prize_fund, $draw->total_prize_fund, $draw->total_prize_fund,
			      $draw->total_prizes_count, $draw->total_prizes_count, $draw->total_prizes_count,
			      $draw->draw_date, PRIZE_CHANGE_DATE)
		)->row();
		if ($rk && (int) $rk->n >= 3) {
			$pool = 'the ' . (int) $rk->n . ' jackpot draws we hold ' . ($new_era ? 'since' : 'before') . ' the 1 September 2026 prize increase';
			if ((int) $rk->fund_equal === 1 && (int) $rk->fund_above === 0) {
				$facts[] = 'At ' . $eur($draw->total_prize_fund) . ', this was the highest total prize fund of ' . $pool . '.';
			} elseif ((int) $rk->fund_equal === 1 && (int) $rk->fund_below === 0) {
				$facts[] = 'At ' . $eur($draw->total_prize_fund) . ', this was the lowest total prize fund of ' . $pool . '.';
			}
			if ((int) $rk->pr_equal === 1 && (int) $rk->pr_above === 0) {
				$facts[] = 'With ' . number_format($draw->total_prizes_count) . ' prizes, this was the most of ' . $pool . '.';
			} elseif ((int) $rk->pr_equal === 1 && (int) $rk->pr_below === 0) {
				$facts[] = 'With ' . number_format($draw->total_prizes_count) . ' prizes, this was the fewest of ' . $pool . '.';
			}
		}

		// 5. Which locations have won the most jackpots so far.
		if ($m >= 4) {
			$tally = array();
			$label = array();
			foreach ($hist as $r) {
				if ($r->location === null) {
					continue;
				}
				$key = strtolower($r->location);
				$tally[$key] = isset($tally[$key]) ? $tally[$key] + 1 : 1;
				$label[$key] = $r->location;
			}
			if (!empty($tally) && max($tally) >= 2) {
				$top_n = max($tally);
				$lead = array();
				foreach ($tally as $key => $n) {
					if ($n === $top_n) {
						$lead[] = $e($label[$key]);
					}
				}
				sort($lead);
				$facts[] = 'Most jackpot wins so far in the draws we hold: ' . $list($lead) . ' (' . $top_n . (count($lead) > 1 ? ' each' : '') . ').';
			}
		}

		if (!empty($facts)) {
			$h[] = '<h2>Worth noting</h2>';
			$h[] = '<ul><li>' . implode('</li><li>', $facts) . '</li></ul>';
		}

		// --- How the jackpot works ------------------------------------
		$h[] = '<h2>How the monthly jackpot works</h2>';
		$h[] = '<p>On the last draw of each calendar month, an additional ' . $eur(self::JACKPOT_PRIZE) . ' jackpot prize is included alongside the usual weekly prizes. '
			. 'See <a href="/how-it-works/">How Prize Bonds Work</a> for the full picture, or the <a href="/stats/odds/">Odds Calculator</a> for what your own holding is worth in the draw.</p>';

		// --- Next steps -----------------------------------------------
		$h[] = '<h2>Check your own bonds</h2>';
		$h[] = '<p>See the <a href="/results/view/' . $draw->draw_date . '/">full results for this draw</a> and the <a href="/results/month/' . $ym . '/">' . $month_label
			. ' report</a>, check your own bond numbers with the <a href="/search/checker/">Prize Bond checker</a>, or read <a href="/how-to-buy-and-cash-in/">how to claim a prize</a>.</p>';
		$h[] = '<p class="text-muted">Prize and winner details come from the official results published by Ireland State Savings. The comparisons are calculated from the draws held in our archive. '
			. 'This site is independent and is not affiliated with the official operator.</p>';

		$body = implode("\n", $h);

		// --- Title, summary, slug -------------------------------------
		$title = $county . ' bond wins ' . '€' . number_format($prize) . ' Prize Bond jackpot – ' . $date_long . ' draw';

		$summary = '';
		$candidates = array(
			'Bond ' . $bond . ' won the €' . number_format($prize) . ' Irish Prize Bond jackpot on ' . $date_long . ', listed under ' . $county . '. See the draw numbers and past jackpot winners.',
			'Bond ' . $bond . ' won the €' . number_format($prize) . ' Irish Prize Bond jackpot on ' . $date_long . ', listed under ' . $county . '.',
			'Who won the €' . number_format($prize) . ' Irish Prize Bond jackpot on ' . $date_long . '? Bond ' . $bond . ', listed under ' . $county . '.',
		);
		foreach ($candidates as $c) {
			if (mb_strlen($c, 'UTF-8') <= 155) {
				$summary = $c;
				break;
			}
		}
		if ($summary === '') {
			$summary = mb_substr($candidates[1], 0, 152, 'UTF-8') . '...';
		}

		$county_slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($county)), '-');
		$slug = trim(self::JACKPOT_PRIZE . '-prize-bond-jackpot-' . $county_slug . '-' . strtolower(date('j-F-Y', strtotime($draw->draw_date))), '-');

		return array('ok' => true, 'error' => '', 'post' => array(
			'title' => $title,
			'slug' => $slug,
			'summary' => $summary,
			'body' => $body,
			'draw_date' => $draw->draw_date,
		));
	}

}
