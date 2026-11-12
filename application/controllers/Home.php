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
			$data['top_tiers'] = get_draw_tiers($this->db, $data['last_draw']->id, 4, true);
		}

		// The home page is what ranks in Google for "prize bonds results" / "checker"
		// (Search Console, 2026-08-30..10-13: ~58% of all impressions, vs 8% for
		// /results/), so its title/description carry the head terms and name the latest
		// published draw. Falls back to the static text if nothing is published yet.
		$latest_published = $this->db->select('draw_date')->from('draws')
			->where('published', 1)->order_by('draw_date', 'desc')->limit(1)->get()->row();

		if ($latest_published) {
			$data['title'] = 'Irish Prize Bonds Results and Checker - ' . date('j M Y', strtotime($latest_published->draw_date)) . ' Draw';
			$data['description'] = 'Irish Prize Bond results for the ' . date('j F Y', strtotime($latest_published->draw_date)) . ' draw, usually posted within hours. Free checker and tracker: check your numbers against every draw we hold.';
		} else {
			$data['title'] = 'Irish Prize Bonds Checker - Results Tracker and Number Search';
			$data['description'] = 'Free Irish Prize Bonds checker and results tracker. Check your Prize Bond numbers against the draws we hold, see the latest results and browse the draw archive.';
		}
		$this->load->view('home', $data);
	}

}
