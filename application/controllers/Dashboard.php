<?php
class Dashboard extends CI_Controller {

	 function __construct()
    {
      parent::__construct();
      $this->load->library('session'); // get_user() below reads it — must load before that call

			if(is_null(get_user())){
				redirect("welcome");
				//var_dump($this->session->userdata('antelope_user'));
			}

    }

	public function Index()
	{
		redirect("overview");
	}

	public function import_winners()
	{
		$page = 'import_winners';
		$data['pageTitle'] = 'Import Winners';
		$data['active_menu'] = 'dashboard/import_winners';

		$this->load->model('antelope');
		$data['draws'] = $this->db->select('id, draw_date, is_jackpot')->from('draws')->order_by('draw_date', 'desc')->get()->result();
		$data['locations'] = $this->db->select('id, name')->from('locations')->order_by('name')->get()->result();
		$data['result'] = null;

		if ($this->input->post('do_import')) {
			$data['result'] = $this->_process_winners_import();
		}

		$this->load->view('header', $data);
		$data["menus"] = get_menus();
		$this->load->view('sidebar', $data);

		if (is_page_permitted($page)) {
			$this->load->view('import_winners', $data);
		} else {
			$this->load->view('not_permitted');
		}

		$this->load->view('footer', $data);
	}

	private function _process_winners_import()
	{
		$draw_id = (int) $this->input->post('draw_id');

		$draw = $this->db->select('id, draw_date')->from('draws')->where('id', $draw_id)->get()->row();
		if (!$draw) {
			return array('errors' => array('Please select a valid draw before importing.'), 'inserted' => 0, 'total_lines' => 0);
		}

		$raw = '';
		if (!empty($_FILES['csv_file']['tmp_name']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
			$raw = file_get_contents($_FILES['csv_file']['tmp_name']);
		} else {
			$raw = $this->input->post('paste_data');
		}

		if (trim((string) $raw) === '') {
			return array('errors' => array('No data provided. Paste CSV data or upload a file.'), 'inserted' => 0, 'total_lines' => 0);
		}

		// Column layout, quoting, BOM, "€1,000.00" values etc. are all handled in
		// parse_winners_import() (general_helper.php) — this method only deals with the database.
		$parsed = parse_winners_import($raw, $draw->draw_date);
		$errors = $parsed['errors'];
		if ($parsed['wrong_date'] > 0) {
			$errors[] = $parsed['wrong_date'] . " row(s) skipped: their draw_date isn't " . $draw->draw_date . ' (the draw selected above)';
		}

		// Preload locations for case-insensitive lookup
		$location_map = array();
		foreach ($this->db->select('id, name')->from('locations')->get()->result() as $loc) {
			$location_map[strtolower(trim($loc->name))] = $loc->id;
		}
		$created_locations = array();

		$batch = array();
		$valid_rows = 0;
		$inserted = 0;
		$batch_size = 1000;

		foreach ($parsed['rows'] as $row) {
			$location_id = null;
			if ($row['location'] !== '') {
				$key = strtolower($row['location']);
				if (!isset($location_map[$key])) {
					// Same as the cron: statesavings.ie reports overseas winners by their actual
					// country/place ("U.K", "Australia"...), so an unseen name is a genuine new
					// location rather than bad data. `name` is UNIQUE, so INSERT IGNORE is safe.
					$this->db->query("INSERT IGNORE INTO locations (name) VALUES (?)", array($row['location']));
					$loc = $this->db->select('id')->from('locations')->where('name', $row['location'])->get()->row();
					if (!$loc) {
						$errors[] = 'Line ' . $row['line'] . ": could not create location \"" . $row['location'] . '" for bond ' . $row['bond'] . ' (row skipped)';
						continue;
					}
					$location_map[$key] = $loc->id;
					$created_locations[] = $row['location'];
				}
				$location_id = $location_map[$key];
			}

			$batch[] = array(
				'draw_id' => $draw_id,
				'bond_number' => $row['bond'],
				'prize_value' => $row['prize'],
				'location_id' => $location_id,
			);
			$valid_rows++;

			if (count($batch) >= $batch_size) {
				db_insert_batch_ignore($this->db, 'draw_winners', $batch);
				$inserted += $this->db->affected_rows();
				$batch = array();
			}
		}

		if (count($batch) > 0) {
			db_insert_batch_ignore($this->db, 'draw_winners', $batch);
			$inserted += $this->db->affected_rows();
		}

		$published = false;
		$draw_total = null;
		if ($inserted > 0) {
			$totals = $this->db->select('COUNT(*) as cnt, SUM(prize_value) as total')->from('draw_winners')->where('draw_id', $draw_id)->get()->row();
			$update = array(
				'total_prizes_count' => $totals->cnt,
				'total_prize_fund' => $totals->total,
			);
			if ($this->input->post('publish_after')) {
				$update['published'] = 1;
				$published = true;
			}
			$this->db->where('id', $draw_id)->update('draws', $update);
			$draw_total = (int) $totals->cnt;
		}

		return array(
			'errors' => $errors,
			'inserted' => $inserted,
			'total_lines' => $parsed['total_lines'],
			'duplicates' => $valid_rows - $inserted, // rows already recorded for this draw (bond numbers are unique per draw)
			'created_locations' => $created_locations,
			'published' => $published,
			'draw_total' => $draw_total,
		);
	}


  public function table($table_name)
  {

			$active_menu = $table_name;
			$page = $table_name;
			$data['pageTitle'] = ucwords(str_replace("_"," ",$table_name));


			if(is_callable(array($this->antelope, $table_name), false, $table_name)){

			  $this->load->helper('xcrud');
			  $xcrud = xcrud_get_instance($table_name . "_" . time());
		      $xcrud->unset_title();

		      $xcrud  = call_user_func_array(array($this->antelope, $table_name),  array($xcrud));

		      $data['table_content'] = $xcrud;

			}else{

				$data['table_content'] = "<div class='alert alert-danger'>
					<h4>Could not find <strong>$active_menu</strong> function in <strong>Application</strong>  > <strong> Models</strong>  > <strong> antelope.php</strong> </h4>
				</div>";

			}

			$data['active_menu'] = "dashboard/table/".$active_menu;
			$this->load->view('header',$data);



			$data["menus"] = get_menus();
			$this->load->view('sidebar',$data);

			if (is_page_permitted($page)) {
					$this->load->view('table',$data);
			}
			else{
					$this->load->view('not_permitted');
			}

			$this->load->view('footer',$data);
	}

}
?>
