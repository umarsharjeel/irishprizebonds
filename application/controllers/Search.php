<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function checker()
	{
		$data['title'] = 'Check Prize Bond Numbers Online - Free Checker | Irish Prize Bonds';
		$data['description'] = 'Check your Irish Prize Bond numbers online for free. Enter up to five numbers — or whole ranges from a certificate — against every published draw result, not just the latest.';
		$data['slots'] = array();
		for ($i = 0; $i < 5; $i++) {
			$data['slots'][$i] = array('first' => '', 'last' => '');
		}
		$data['results'] = null;
		$data['errors'] = array();

		if ($this->input->post('do_check')) {
			$clean = array();
			for ($i = 0; $i < 5; $i++) {
				$first = strtoupper(trim((string) $this->input->post('first_' . $i)));
				$last = strtoupper(trim((string) $this->input->post('last_' . $i)));
				$data['slots'][$i] = array('first' => $first, 'last' => $last);

				if ($first === '') {
					continue;
				}
				if ($last === '') {
					$clean[] = $first;
					continue;
				}

				$result = expand_bond_range($first, $last, 5000);
				if (isset($result['error'])) {
					$data['errors'][] = 'Row ' . ($i + 1) . ': ' . $result['error'] . '.';
				} else {
					$clean = array_merge($clean, $result['numbers']);
				}
			}
			$clean = array_values(array_unique($clean));
			if (count($clean) > 5000) {
				$data['errors'][] = 'Only checked the first 5,000 numbers (including expanded ranges) — that\'s the most we can check in one go.';
				$clean = array_slice($clean, 0, 5000);
			}

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
		$data['description'] = 'Search a long list of Prize Bond numbers, or whole number ranges, against all published draw results in one pass.';
		$data['list'] = '';
		$data['results'] = null;
		$data['searched_count'] = 0;
		$data['errors'] = array();

		if ($this->input->post('do_search')) {
			$raw = (string) $this->input->post('list');
			$data['list'] = $raw;

			$expanded = expand_bond_list_with_ranges($raw, 5000);
			$numbers = $expanded['numbers'];
			$data['errors'] = $expanded['errors'];
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