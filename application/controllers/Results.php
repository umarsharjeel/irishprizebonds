<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Results extends CI_Controller {

	private $per_page = 20;

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
