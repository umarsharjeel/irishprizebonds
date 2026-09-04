<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sitemap extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{
		header('Content-Type: application/xml; charset=utf-8');

		$static_pages = array(
			'' => '1.0',
			'results' => '0.9',
			'results/archive' => '0.8',
			'schedule' => '0.7',
			'search/checker' => '0.8',
			'search/power' => '0.7',
			'stats/winners' => '0.7',
			'stats/counties' => '0.7',
			'stats/odds' => '0.7',
			'how-it-works' => '0.7',
			'how-to-buy-and-cash-in' => '0.6',
			'are-prize-bonds-worth-it' => '0.6',
			'history' => '0.6',
			'faq' => '0.7',
			'about' => '0.4',
			'contact-us' => '0.4',
			'privacy-policy' => '0.3',
		);

		$draws = $this->db->select('draw_date, updated_at')->from('draws')->where('published', 1)->order_by('draw_date', 'desc')->get()->result();

		$months = $this->db->query("SELECT DISTINCT DATE_FORMAT(draw_date, '%Y-%m') as ym FROM draws WHERE published = 1 ORDER BY ym DESC")->result();

		echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		foreach ($static_pages as $path => $priority) {
			$loc = $path === '' ? base_url() : base_url($path . '/');
			echo '<url><loc>' . $loc . '</loc><priority>' . $priority . '</priority></url>' . "\n";
		}

		foreach ($months as $m) {
			echo '<url><loc>' . base_url('results/month/' . $m->ym . '/') . '</loc><priority>0.6</priority></url>' . "\n";
		}

		foreach ($draws as $d) {
			echo '<url><loc>' . base_url('results/view/' . $d->draw_date . '/') . '</loc>';
			if ($d->updated_at) {
				echo '<lastmod>' . date('Y-m-d', strtotime($d->updated_at)) . '</lastmod>';
			}
			echo '<priority>0.5</priority></url>' . "\n";
		}

		echo '</urlset>';
	}

	public function robots()
	{
		header('Content-Type: text/plain; charset=utf-8');
		echo "User-Agent: *\n";
		echo "Disallow: /dashboard\n";
		echo "Disallow: /welcome\n";
		echo "Disallow: /user_management\n";
		echo "Disallow: /cron\n";
		echo "Disallow: /xcrud_ajax\n";
		echo "\n";
		echo "Sitemap: " . base_url('sitemap.xml') . "\n";
	}

}
