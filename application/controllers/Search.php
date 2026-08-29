<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function checker()
	{
		$data['title'] = 'Check Your Prize Bond Numbers | Irish Prize Bonds';
		$data['description'] = 'Check up to five Prize Bond numbers against all published draw results.';
		$data['numbers'] = array('', '', '', '', '');
		$data['results'] = null;

		if ($this->input->post('do_check')) {
			$numbers = array();
			for ($i = 0; $i < 5; $i++) {
				$numbers[$i] = strtoupper(trim((string) $this->input->post('number_' . $i)));
			}
			$data['numbers'] = $numbers;

			$clean = array_values(array_filter($numbers, function ($n) { return $n !== ''; }));
			if (!empty($clean)) {
				$data['results'] = $this->db->select('draw_winners.bond_number, draw_winners.prize_value, draws.draw_date, locations.name as location')
					->from('draw_winners')
					->join('draws', 'draws.id = draw_winners.draw_id')
					->join('locations', 'locations.id = draw_winners.location_id', 'left')
					->where_in('draw_winners.bond_number', $clean)
					->where('draws.published', 1)
					->order_by('draws.draw_date', 'desc')
					->get()->result();
			} else {
				$data['results'] = array();
			}
		}

		$this->load->view('search_checker', $data);
	}

	public function power()
	{
		$data['title'] = 'Power Search | Irish Prize Bonds';
		$data['description'] = 'Search a list of Prize Bond numbers against all published draw results.';
		$data['list'] = '';
		$data['results'] = null;
		$data['searched_count'] = 0;

		if ($this->input->post('do_search')) {
			$raw = (string) $this->input->post('list');
			$data['list'] = $raw;

			$parts = preg_split('/[\s,]+/', strtoupper(trim($raw)));
			$numbers = array_values(array_unique(array_filter($parts, function ($n) { return $n !== ''; })));
			$numbers = array_slice($numbers, 0, 500);
			$data['searched_count'] = count($numbers);

			if (!empty($numbers)) {
				$data['results'] = $this->db->select('draw_winners.bond_number, draw_winners.prize_value, draws.draw_date, locations.name as location')
					->from('draw_winners')
					->join('draws', 'draws.id = draw_winners.draw_id')
					->join('locations', 'locations.id = draw_winners.location_id', 'left')
					->where_in('draw_winners.bond_number', $numbers)
					->where('draws.published', 1)
					->order_by('draws.draw_date', 'desc')
					->get()->result();
			} else {
				$data['results'] = array();
			}
		}

		$this->load->view('search_power', $data);
	}

}
