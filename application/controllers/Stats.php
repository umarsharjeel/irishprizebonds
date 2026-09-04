<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stats extends CI_Controller {

	private $per_page = 25;

	function __construct()
	{
		parent::__construct();
	}

	public function winners()
	{
		$page = max(1, (int) $this->input->get('page'));
		$threshold = 50000;

		$query = $this->db->select('draw_winners.bond_number, draw_winners.prize_value, draws.draw_date, draws.is_jackpot, locations.name as location')
			->from('draw_winners')
			->join('draws', 'draws.id = draw_winners.draw_id')
			->join('locations', 'locations.id = draw_winners.location_id', 'left')
			->where('draws.published', 1)
			->where('draw_winners.prize_value >=', $threshold);

		$count_query = clone $query;
		$total_rows = $count_query->count_all_results('', false);
		$total_pages = max(1, ceil($total_rows / $this->per_page));
		$page = min($page, $total_pages);

		$data['winners'] = $query->order_by('draws.draw_date', 'desc')->order_by('draw_winners.prize_value', 'desc')
			->limit($this->per_page, ($page - 1) * $this->per_page)->get()->result();

		$totals = $this->db->select('COUNT(*) as cnt, SUM(prize_value) as total')->from('draw_winners')
			->join('draws', 'draws.id = draw_winners.draw_id')
			->where('draws.published', 1)->where('draw_winners.prize_value >=', $threshold)->get()->row();

		$data['total_rows'] = $total_rows;
		$data['total_value'] = $totals->total;
		$data['page'] = $page;
		$data['total_pages'] = $total_pages;

		$data['title'] = 'Prize Bond Winners List - Every Big Winner | Irish Prize Bonds';
		$data['description'] = 'A running list of Irish Prize Bond winners: every €50,000 top prize and €500,000 monthly jackpot winner across all the draws we track, with dates and locations.';
		$this->load->view('stats_winners', $data);
	}

	public function counties()
	{
		$rows = $this->db->select('locations.name, COUNT(*) as win_count, SUM(draw_winners.prize_value) as total_value')
			->from('draw_winners')
			->join('draws', 'draws.id = draw_winners.draw_id')
			->join('locations', 'locations.id = draw_winners.location_id')
			->where('draws.published', 1)
			->group_by('locations.id')
			->order_by('win_count', 'desc')
			->get()->result();

		$max_count = 0;
		foreach ($rows as $r) {
			if ($r->win_count > $max_count) $max_count = $r->win_count;
		}

		$data['rows'] = $rows;
		$data['max_count'] = $max_count;
		$data['title'] = 'County Prize Bond Statistics | Irish Prize Bonds';
		$data['description'] = 'Which Irish county wins the most Prize Bond prizes? Cumulative winner statistics by county across every draw we track.';
		$this->load->view('stats_counties', $data);
	}

	public function odds()
	{
		// Reference figures derived from publicly reported odds (Irish press coverage),
		// not official statesavings.ie data — the exact current total isn't published.
		// ~141,000,000 : 1 odds of a €25 (4-bond) holding winning the top prize in a
		// single draw implies an assumed pool of ~4 * 141,000,000 bonds in circulation.
		$assumed_total_bonds = 564000000;
		$avg_prizes_per_regular_draw = 8633; // from a real observed regular (non-jackpot) draw

		$bonds = (int) $this->input->get('bonds');
		$result = null;
		if ($bonds > 0) {
			$bonds = min($bonds, 40000);
			$odds_top_prize_per_draw = $bonds / $assumed_total_bonds;
			$odds_any_prize_per_draw = min(1, ($bonds * $avg_prizes_per_regular_draw) / $assumed_total_bonds);
			$odds_any_prize_per_year = 1 - pow(1 - $odds_any_prize_per_draw, 52);

			$result = array(
				'bonds' => $bonds,
				'euro_value' => $bonds * 6.25,
				'top_prize_per_draw' => $odds_top_prize_per_draw,
				'any_prize_per_draw' => $odds_any_prize_per_draw,
				'any_prize_per_year' => $odds_any_prize_per_year,
			);
		}

		$data['result'] = $result;
		$data['bonds'] = $bonds;
		$data['title'] = 'Prize Bond Odds Calculator | Irish Prize Bonds';
		$data['description'] = 'Estimate your odds of winning an Irish Prize Bond prize based on how many bonds you hold, using publicly reported reference figures.';
		$this->load->view('stats_odds', $data);
	}

}
