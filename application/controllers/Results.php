<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Results extends CI_Controller {

	private $per_page = 20;

	// Prize fund and tiers were increased from this date (top regular prize
	// €50k -> €100k, base prize €75 -> €100; see how_it_works.php). Prize totals
	// from either side of it aren't like-for-like, so any comparison between
	// draws is scoped to the same side via _where_same_era().
	const PRIZE_CHANGE_DATE = '2026-09-01';

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Stable "Irish Prize Bond Results" landing page.
	 *
	 * Previously this 301-redirected straight to the latest dated draw, which
	 * left the recurring "prize bonds results" / "irish prize bonds results"
	 * search intent with no permanent URL to rank — the target changed address
	 * every week. This hub keeps a fixed URL, leads with the newest published
	 * draw, and lists recent draws, so it can accrue ranking signal over time
	 * while still getting the visitor to today's numbers in one click.
	 */
	public function index()
	{
		$latest = $this->db->select('*')->from('draws')->where('published', 1)->order_by('draw_date', 'desc')->limit(1)->get()->row();

		if (!$latest) {
			$data['title'] = 'Irish Prize Bond Results | Irish Prize Bonds';
			$data['description'] = 'Latest Irish Prize Bond draw results.';
			$this->load->view('results_empty', $data);
			return;
		}

		$data['latest'] = $latest;
		$data['top_tiers'] = $this->db->select('prize_value, prize_count')->from('draw_prize_tiers')
			->where('draw_id', $latest->id)->order_by('prize_value', 'desc')->limit(5)->get()->result();
		$data['recent'] = $this->db->select('draw_date, is_jackpot, total_prize_fund, total_prizes_count')
			->from('draws')->where('published', 1)->order_by('draw_date', 'desc')->limit(13)->get()->result();

		$latest_label = date('j F Y', strtotime($latest->draw_date));
		// Static title on purpose: this page's value is being a fixed target for
		// "irish prize bond results", so the <title> must not churn week to week
		// (and must stay distinct from the dated results/view/<date> title). The
		// live draw date lives in the H1/body and meta description instead.
		$data['title'] = 'Irish Prize Bond Results - Latest and Recent Draws | Irish Prize Bonds';
		$data['description'] = 'Latest Irish Prize Bond results from the ' . $latest_label . ' draw, updated after every weekly draw, plus a list of recent draws and the full archive.';

		$this->load->view('results_hub', $data);
	}

	public function archive()
	{
		$data['draws'] = $this->db->select('id, draw_date, is_jackpot, total_prize_fund, total_prizes_count, published')
			->from('draws')->order_by('draw_date', 'desc')->get()->result();
		$data['title'] = 'Prize Bond Draw Archive | Irish Prize Bonds';
		$data['description'] = 'Browse past and upcoming Irish Prize Bond draws.';
		$this->load->view('results_archive', $data);
	}

	public function month($year_month = null)
	{
		if (!$year_month || !preg_match('/^(\d{4})-(\d{2})$/', $year_month, $m)) {
			show_404();
			return;
		}

		$draws = $this->db->select('id, draw_date, is_jackpot, total_prize_fund, total_prizes_count')
			->from('draws')
			->where('published', 1)
			->where("DATE_FORMAT(draw_date, '%Y-%m') =", $year_month)
			->order_by('draw_date')
			->get()->result();

		if (empty($draws)) {
			show_404();
			return;
		}

		$month_label = date('F Y', strtotime($year_month . '-01'));

		$total_fund = 0;
		$total_prizes = 0;
		foreach ($draws as $d) {
			$total_fund += $d->total_prize_fund;
			$total_prizes += $d->total_prizes_count;
		}

		$prev = $this->db->query("SELECT DATE_FORMAT(MAX(draw_date), '%Y-%m') as ym FROM draws WHERE published = 1 AND draw_date < ?", array($year_month . '-01'))->row();
		$next = $this->db->query("SELECT DATE_FORMAT(MIN(draw_date), '%Y-%m') as ym FROM draws WHERE published = 1 AND draw_date >= ?", array(date('Y-m-01', strtotime($year_month . '-01 +1 month'))))->row();

		$data['draws'] = $draws;
		$data['month_label'] = $month_label;
		$data['year_month'] = $year_month;
		$data['total_fund'] = $total_fund;
		$data['total_prizes'] = $total_prizes;
		$data['prev_month'] = $prev && $prev->ym ? $prev->ym : null;
		$data['next_month'] = $next && $next->ym ? $next->ym : null;

		$data['title'] = $month_label . ' Prize Bond Results | Irish Prize Bonds';
		$data['description'] = 'All Irish Prize Bond draw results for ' . $month_label . ': draw dates, prize funds and winner counts.';

		$this->load->view('results_month', $data);
	}

	public function view($draw_date = null)
	{
		$draw = $this->db->select('*')->from('draws')->where('draw_date', $draw_date)->get()->row();
		if (!$draw) {
			show_404();
			return;
		}

		if (!$draw->published) {
			$is_future = $draw->draw_date > date('Y-m-d');
			$data['draw'] = $draw;
			$data['is_future'] = $is_future;
			$data['title'] = ($is_future ? 'Upcoming Draw ' : 'Draw Result ') . date('d F Y', strtotime($draw->draw_date)) . ' | Irish Prize Bonds';
			$data['description'] = $is_future
				? 'Irish Prize Bond draw scheduled for ' . date('d F Y', strtotime($draw->draw_date)) . '.'
				: 'Results for the Irish Prize Bond draw held on ' . date('d F Y', strtotime($draw->draw_date)) . ' are pending and will be updated soon.';
			$data = array_merge($data, $this->_pending_context($draw));
			$this->load->view('results_pending', $data);
			return;
		}

		$tiers = $this->db->select('prize_value, prize_count')->from('draw_prize_tiers')
			->where('draw_id', $draw->id)->order_by('sort_order')->order_by('prize_value', 'desc')->get()->result();
		if (empty($tiers)) {
			$tiers = $this->db->select('prize_value, COUNT(*) as prize_count')->from('draw_winners')
				->where('draw_id', $draw->id)->group_by('prize_value')->order_by('prize_value', 'desc')->get()->result();
		}

		$data['summary'] = $this->_build_draw_summary($draw, $tiers);
		$data['analysis'] = $this->_build_draw_analysis($draw, $tiers);

		$page = max(1, (int) $this->input->get('page'));
		$search = trim((string) $this->input->get('q'));
		$location_id = (int) $this->input->get('location');
		$sort = $this->input->get('sort') ?: 'prize_desc';

		$query = $this->db->select('draw_winners.bond_number, draw_winners.prize_value, locations.name as location')
			->from('draw_winners')
			->join('locations', 'locations.id = draw_winners.location_id', 'left')
			->where('draw_winners.draw_id', $draw->id);

		if ($search !== '') {
			$query->like('draw_winners.bond_number', strtoupper($search), 'after');
		}
		if ($location_id) {
			$query->where('draw_winners.location_id', $location_id);
		}

		$count_query = clone $query;
		$total_rows = $count_query->count_all_results('', false);

		switch ($sort) {
			case 'prize_asc':
				$query->order_by('draw_winners.prize_value', 'asc');
				break;
			case 'bond':
				$query->order_by('draw_winners.bond_number', 'asc');
				break;
			case 'location':
				$query->order_by('locations.name', 'asc');
				break;
			case 'prize_desc':
			default:
				$query->order_by('draw_winners.prize_value', 'desc');
				break;
		}
		$query->order_by('draw_winners.id', 'asc');

		$total_pages = max(1, ceil($total_rows / $this->per_page));
		$page = min($page, $total_pages);
		$query->limit($this->per_page, ($page - 1) * $this->per_page);

		$data['winners'] = $query->get()->result();
		$data['draw'] = $draw;
		$data['tiers'] = $tiers;
		$data['locations'] = $this->db->select('id, name')->from('locations')->order_by('name')->get()->result();
		$data['total_rows'] = $total_rows;
		$data['page'] = $page;
		$data['total_pages'] = $total_pages;
		$data['search'] = $search;
		$data['location_id'] = $location_id;
		$data['sort'] = $sort;

		$data['prev_draw'] = $this->db->select('draw_date')->from('draws')->where('draw_date <', $draw_date)->order_by('draw_date', 'desc')->limit(1)->get()->row();
		$data['next_draw'] = $this->db->select('draw_date')->from('draws')->where('draw_date >', $draw_date)->order_by('draw_date', 'asc')->limit(1)->get()->row();

		$data['title'] = 'Irish Prize Bond Results - ' . date('j F Y', strtotime($draw->draw_date)) . ' Draw | Irish Prize Bonds';
		$data['description'] = 'Full Irish Prize Bond results for the ' . date('j F Y', strtotime($draw->draw_date)) . ' draw: top prize winners, the complete prize breakdown and winning bond numbers by location.';

		$this->load->view('results_view', $data);
	}

	/**
	 * Real context for a draw that has no results yet, so the page isn't just
	 * a one-line placeholder. Everything here comes from draws we have already
	 * published — the most recent draw of the same type (jackpot vs regular)
	 * with its prize breakdown, how that type of draw has paid out across all
	 * tracked draws, year-to-date totals, and the latest results — and so
	 * differs from one pending draw to the next.
	 */
	private function _pending_context($draw)
	{
		$is_jackpot = (int) $draw->is_jackpot;
		$ctx = array();

		$this->db->select('id, draw_date, is_jackpot, total_prize_fund, total_prizes_count')
			->from('draws')->where('published', 1)->where('is_jackpot', $is_jackpot)
			->where('draw_date <', $draw->draw_date);
		$this->_where_same_era($draw->draw_date);
		$ctx['last_same_type'] = $this->db->order_by('draw_date', 'desc')->limit(1)->get()->row();
		$ctx['new_era'] = $draw->draw_date >= self::PRIZE_CHANGE_DATE;
		$ctx['era_label'] = $ctx['new_era'] ? 'since the 1 September 2026 prize increase' : 'before the 1 September 2026 prize increase';

		$ctx['last_same_type_tiers'] = array();
		if ($ctx['last_same_type']) {
			$ctx['last_same_type_tiers'] = $this->db->select('prize_value, prize_count')->from('draw_prize_tiers')
				->where('draw_id', $ctx['last_same_type']->id)
				->order_by('sort_order')->order_by('prize_value', 'desc')->limit(6)->get()->result();
		}

		$this->db->select(
				'COUNT(*) AS n, AVG(total_prize_fund) AS avg_fund, MIN(total_prize_fund) AS min_fund, MAX(total_prize_fund) AS max_fund, AVG(total_prizes_count) AS avg_prizes',
				false
			)
			->from('draws')->where('published', 1)->where('is_jackpot', $is_jackpot)->where('total_prize_fund >', 0);
		$this->_where_same_era($draw->draw_date);
		$ctx['type_stats'] = $this->db->get()->row();

		$year = (int) date('Y', strtotime($draw->draw_date));
		$ctx['year'] = $year;
		$ctx['year_stats'] = $this->db->select('COUNT(*) AS n, SUM(total_prize_fund) AS fund, SUM(total_prizes_count) AS prizes', false)
			->from('draws')->where('published', 1)->where('YEAR(draw_date) =', $year, false)
			->get()->row();

		$ctx['recent'] = $this->db->select('draw_date, is_jackpot, total_prize_fund, total_prizes_count')
			->from('draws')->where('published', 1)->order_by('draw_date', 'desc')->limit(5)->get()->result();

		$today = new DateTime(date('Y-m-d'));
		$ctx['days_until'] = (int) $today->diff(new DateTime($draw->draw_date))->format('%r%a');
		$ctx['is_friday'] = date('N', strtotime($draw->draw_date)) == 5;

		return $ctx;
	}

	/**
	 * Restricts the query being built (on the draws table) to draws on the same
	 * side of the prize-structure change as $date.
	 */
	private function _where_same_era($date)
	{
		if ($date >= self::PRIZE_CHANGE_DATE) {
			$this->db->where('draw_date >=', self::PRIZE_CHANGE_DATE);
		} else {
			$this->db->where('draw_date <', self::PRIZE_CHANGE_DATE);
		}
	}

	/**
	 * Original, per-draw analysis computed from our own tables — not text taken
	 * from the official site. Every comparison is against draws of the same
	 * type (jackpot vs regular) and the same prize structure, and each section
	 * is left out when there isn't enough comparable data to say anything real.
	 */
	private function _build_draw_analysis($draw, $tiers)
	{
		$is_jackpot = (int) $draw->is_jackpot;
		$new_era = $draw->draw_date >= self::PRIZE_CHANGE_DATE;
		$a = array(
			'type_label' => $is_jackpot ? 'jackpot' : 'regular',
			'new_era' => $new_era,
			'era_label' => $new_era ? 'since the 1 September 2026 prize increase' : 'before the 1 September 2026 prize increase',
		);

		// Previous draw of the same type under the same prize structure.
		$this->db->select('draw_date, total_prize_fund, total_prizes_count')
			->from('draws')->where('published', 1)->where('is_jackpot', $is_jackpot)
			->where('draw_date <', $draw->draw_date);
		$this->_where_same_era($draw->draw_date);
		$a['comparator'] = $this->db->order_by('draw_date', 'desc')->limit(1)->get()->row();

		// True when nothing has been published under the new structure before this draw.
		$a['first_since_change'] = false;
		if ($new_era) {
			$earlier = $this->db->from('draws')->where('published', 1)
				->where('draw_date >=', self::PRIZE_CHANGE_DATE)->where('draw_date <', $draw->draw_date)
				->count_all_results();
			$a['first_since_change'] = ($earlier === 0);
		}

		// Rank among comparable draws (same type, same structure), by prize fund and by prize count.
		$op = $new_era ? '>=' : '<';
		$rank = $this->db->query(
			"SELECT COUNT(*) AS m,
			        SUM(total_prize_fund > ?) AS fund_above, SUM(total_prize_fund = ?) AS fund_equal,
			        SUM(total_prizes_count > ?) AS prizes_above, SUM(total_prizes_count = ?) AS prizes_equal
			 FROM draws WHERE published = 1 AND is_jackpot = ? AND total_prize_fund > 0 AND draw_date {$op} ?",
			array($draw->total_prize_fund, $draw->total_prize_fund, $draw->total_prizes_count, $draw->total_prizes_count,
			      $is_jackpot, self::PRIZE_CHANGE_DATE)
		)->row();
		$a['rank'] = null;
		if ($rank && (int) $rank->m >= 3) {
			$a['rank'] = array(
				'of' => (int) $rank->m,
				'by_fund' => (int) $rank->fund_above + 1,
				// "equal" includes this draw itself, so subtract it to get the number of other draws it ties with.
				'fund_tied' => max(0, (int) $rank->fund_equal - 1),
				'by_prizes' => (int) $rank->prizes_above + 1,
				'prizes_tied' => max(0, (int) $rank->prizes_equal - 1),
			);
		}

		// Where the prize money went, by tier.
		$a['money'] = null;
		if (!empty($tiers)) {
			$sorted = $tiers;
			usort($sorted, function ($x, $y) { return $y->prize_value <=> $x->prize_value; });
			$top = $sorted[0];
			$base = $sorted[count($sorted) - 1];
			$total_value = 0;
			$total_count = 0;
			$big_count = 0;
			foreach ($sorted as $t) {
				$total_value += $t->prize_value * $t->prize_count;
				$total_count += $t->prize_count;
				if ($t->prize_value >= 1000) {
					$big_count += $t->prize_count;
				}
			}
			if ($total_value > 0 && $total_count > 0) {
				$a['money'] = array(
					'top_value' => $top->prize_value,
					'top_count' => (int) $top->prize_count,
					'top_share' => 100 * $top->prize_value * $top->prize_count / $total_value,
					'base_value' => $base->prize_value,
					'base_count' => (int) $base->prize_count,
					'base_count_share' => 100 * $base->prize_count / $total_count,
					'base_value_share' => 100 * $base->prize_value * $base->prize_count / $total_value,
					'avg_prize' => $total_value / $total_count,
					'big_count' => $big_count,
					'total_count' => $total_count,
					'single_tier' => count($sorted) === 1,
				);
			}
		}

		// Where the winners are from, for this draw only.
		$loc_rows = $this->db->select('locations.name AS name, COUNT(*) AS n', false)
			->from('draw_winners')->join('locations', 'locations.id = draw_winners.location_id', 'left')
			->where('draw_winners.draw_id', $draw->id)
			->group_by('draw_winners.location_id')->order_by('n', 'desc')->order_by('locations.name', 'asc')
			->get()->result();

		$located_total = 0;
		$unlocated = 0;
		$located = array();
		foreach ($loc_rows as $r) {
			if ($r->name === null) {
				$unlocated += (int) $r->n;
			} else {
				$located[] = $r;
				$located_total += (int) $r->n;
			}
		}
		$a['geo'] = null;
		if ($located_total > 0) {
			$a['geo'] = array(
				'distinct' => count($located),
				'located_total' => $located_total,
				'unlocated' => $unlocated,
				'top' => array_slice($located, 0, 5),
			);

			// Locations of the larger prizes (EUR 1,000 and over).
			$big_rows = $this->db->select('locations.name AS name, COUNT(*) AS n', false)
				->from('draw_winners')->join('locations', 'locations.id = draw_winners.location_id', 'left')
				->where('draw_winners.draw_id', $draw->id)->where('draw_winners.prize_value >=', 1000)
				->group_by('draw_winners.location_id')->order_by('n', 'desc')->order_by('locations.name', 'asc')
				->get()->result();
			$big_total = 0;
			$big_located = array();
			foreach ($big_rows as $r) {
				$big_total += (int) $r->n;
				if ($r->name !== null) {
					$big_located[] = $r;
				}
			}
			$a['geo']['big_total'] = $big_total;
			$a['geo']['big_top'] = array_slice($big_located, 0, 6);
			$a['geo']['big_distinct'] = count($big_located);
		}

		return $a;
	}

	/**
	 * A short, per-draw summary paragraph built from real tier/winner data —
	 * genuinely different draw to draw, not filler. Exists mainly so the
	 * (numerous, weekly-multiplying) single-draw-result page template isn't
	 * just a heading + a data table, which reads as thin/templated content
	 * at scale to both search engines and ad-network content reviews.
	 */
	private function _build_draw_summary($draw, $tiers)
	{
		if (empty($tiers)) {
			return '';
		}

		$date_label = date('d F Y', strtotime($draw->draw_date));
		$type = $draw->is_jackpot ? 'jackpot' : 'regular weekly';

		$top = $tiers[0];
		$top_winner = $this->db->select('locations.name as name')->from('draw_winners')
			->join('locations', 'locations.id = draw_winners.location_id', 'left')
			->where('draw_winners.draw_id', $draw->id)
			->where('draw_winners.prize_value', $top->prize_value)
			->limit(1)->get()->row();

		$summary = "This {$type} draw on {$date_label} awarded " . number_format($draw->total_prizes_count)
			. " prizes worth a total of &euro;" . number_format($draw->total_prize_fund) . ". ";

		if ((int) $top->prize_count === 1) {
			$summary .= "The top prize of &euro;" . number_format($top->prize_value) . " went to a single bond number";
			if ($top_winner && $top_winner->name) {
				$summary .= " in " . htmlspecialchars($top_winner->name);
			}
			$summary .= ".";
		} else {
			$summary .= number_format($top->prize_count) . " winners each took home &euro;" . number_format($top->prize_value) . ".";
		}

		if (isset($tiers[1])) {
			$second = $tiers[1];
			$plural = ((int) $second->prize_count === 1) ? array('prize', 'was') : array('prizes', 'were');
			$summary .= " A further " . number_format($second->prize_count) . " {$plural[0]} of &euro;" . number_format($second->prize_value) . " {$plural[1]} also awarded.";
		}

		return $summary;
	}

}
