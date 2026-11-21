<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public side of the jackpot write-ups (see Post_model). /news/ lists them and
 * /news/{slug}/ shows one. Both work, and show nothing, before the posts table exists.
 */
class News extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('post_model');
	}

	public function index()
	{
		$data['posts'] = $this->post_model->list_published(60);
		$data['title'] = 'Irish Prize Bond Jackpot Write-ups | Irish Prize Bonds';
		$data['description'] = 'Write-ups of each monthly €500,000 Irish Prize Bond jackpot draw: who won, how the draw compares and the jackpot history in the draws we hold.';
		$this->load->view('news_index', $data);
	}

	public function view($slug = '')
	{
		$post = $this->post_model->find_published_by_slug($slug);
		if (!$post) {
			show_404();
			return;
		}

		$draw = null;
		if ($post->draw_id) {
			$draw = $this->db->select('draw_date')->from('draws')->where('id', (int) $post->draw_id)->get()->row();
		}

		$data['post'] = $post;
		$data['draw'] = $draw;
		$data['neighbours'] = $this->post_model->neighbours($post);
		// The headline already carries the date and county; only add the site name when that keeps the tag a sensible length.
		$with_site = $post->title . ' | Irish Prize Bonds';
		$data['title'] = (mb_strlen($with_site, 'UTF-8') <= 75) ? $with_site : $post->title;
		$data['description'] = $post->summary;
		$this->load->view('news_post', $data);
	}

}
