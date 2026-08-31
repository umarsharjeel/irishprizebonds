<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index(){
		$today = date('Y-m-d');

		$data['last_draw'] = $this->db->select('*')->from('draws')
			->where('draw_date <=', $today)->order_by('draw_date', 'desc')->limit(1)->get()->row();

		// draw_date >= today (not >), with published = 0 doing the actual retiring:
		// on the draw's own calendar day, before results are posted, it's still
		// genuinely "the next draw" — a strict > wrongly shows "no upcoming draw"
		// for the entire day. published = 0 is what correctly excludes it once
		// it's actually been imported. (Same fix as Content::schedule().)
		$data['next_draw'] = $this->db->select('*')->from('draws')
			->where('draw_date >=', $today)->where('published', 0)
			->order_by('draw_date', 'asc')->limit(1)->get()->row();

		$data['top_tiers'] = array();
		if ($data['last_draw'] && $data['last_draw']->published) {
			$data['top_tiers'] = $this->db->select('prize_value, prize_count')->from('draw_prize_tiers')
				->where('draw_id', $data['last_draw']->id)->order_by('prize_value', 'desc')->limit(4)->get()->result();
		}

		$data['title'] = 'Irish Prize Bonds - Draw Results & Number Checker';
		$data['description'] = 'Check Irish Prize Bond draw results, browse the draw archive and search your Prize Bond numbers.';
		$this->load->view('home', $data);
	}

}
