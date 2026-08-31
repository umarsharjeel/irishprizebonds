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
		$errors = array();
		$inserted = 0;

		$draw = $this->db->select('id')->from('draws')->where('id', $draw_id)->get()->row();
		if (!$draw) {
			return array('errors' => array('Please select a valid draw before importing.'), 'inserted' => 0, 'total_lines' => 0);
		}

		$raw = '';
		if (!empty($_FILES['csv_file']['tmp_name']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
			$raw = file_get_contents($_FILES['csv_file']['tmp_name']);
		} else {
			$raw = $this->input->post('paste_data');
		}

		$raw = trim((string) $raw);
		if ($raw === '') {
			return array('errors' => array('No data provided. Paste CSV data or upload a file.'), 'inserted' => 0, 'total_lines' => 0);
		}

		// Normalize line endings and split
		$lines = preg_split('/\r\n|\r|\n/', $raw);
		$lines = array_filter($lines, function ($l) { return trim($l) !== ''; });
		$lines = array_values($lines);
		$total_lines = count($lines);

		// Preload locations for case-insensitive lookup
		$locations_result = $this->db->select('id, name')->from('locations')->get()->result();
		$location_map = array();
		foreach ($locations_result as $loc) {
			$location_map[strtolower(trim($loc->name))] = $loc->id;
		}

		// Detect and skip a header row: if 2nd column of first line isn't numeric
		$first_cols = preg_split('/[,\t]/', $lines[0]);
		if (isset($first_cols[1]) && !is_numeric(str_replace(array('€', ','), '', trim($first_cols[1])))) {
			array_shift($lines);
		}

		$batch = array();
		$batch_size = 1000;
		$line_no = $total_lines - count($lines); // account for header offset in reporting

		foreach ($lines as $line) {
			$line_no++;
			$cols = preg_split('/[,\t]/', $line);
			$bond_number = isset($cols[0]) ? strtoupper(trim($cols[0])) : '';
			$prize_value_raw = isset($cols[1]) ? trim($cols[1]) : '';
			$location_name = isset($cols[2]) ? trim($cols[2]) : '';

			$prize_value = str_replace(array('€', ','), '', $prize_value_raw);

			if (!preg_match('/^[A-Z0-9]{4,10}$/', $bond_number)) {
				$errors[] = "Line $line_no: invalid bond number \"$bond_number\"";
				continue;
			}
			if ($prize_value === '' || !is_numeric($prize_value)) {
				$errors[] = "Line $line_no: invalid prize value \"$prize_value_raw\" for bond $bond_number";
				continue;
			}

			$location_id = null;
			if ($location_name !== '') {
				$key = strtolower($location_name);
				if (isset($location_map[$key])) {
					$location_id = $location_map[$key];
				} else {
					$errors[] = "Line $line_no: unrecognized location \"$location_name\" for bond $bond_number (row skipped)";
					continue;
				}
			}

			$batch[] = array(
				'draw_id' => $draw_id,
				'bond_number' => $bond_number,
				'prize_value' => $prize_value,
				'location_id' => $location_id,
			);

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

		if ($inserted > 0) {
			$totals = $this->db->select('COUNT(*) as cnt, SUM(prize_value) as total')->from('draw_winners')->where('draw_id', $draw_id)->get()->row();
			$this->db->where('id', $draw_id)->update('draws', array(
				'total_prizes_count' => $totals->cnt,
				'total_prize_fund' => $totals->total,
			));
		}

		return array('errors' => $errors, 'inserted' => $inserted, 'total_lines' => $total_lines);
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
