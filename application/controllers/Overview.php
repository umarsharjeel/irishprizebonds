<?php
class Overview extends CI_Controller {

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

    $data['pageTitle'] = "Dashboard Overview";
    $data['active_menu'] = "dashboard/overview";
    $this->load->view('header',$data);
    $data["menus"] = get_menus();
    $this->load->view('sidebar',$data);

    //Boxes and graphs start here------------------------------

     $boxes = array();
     // $boxes[] = stats_box("Total Apps", "app_downloads","COUNT(id)","3");
     // $boxes[] = stats_box("Total Admins", "antelope_users","COUNT(email)","3", array('user_type' => 'superadmin'));
     // $boxes[] = stats_box("Total Money", "app_downloads","SUM(price)");
     // $boxes[] = stats_box("Facebook Money", "app_downloads","SUM(price)","3",array('app_name' => 'facebook'));

     // Cron::_mark_live_check_done() (see application/controllers/Cron.php) records
     // this every time the cron actually checks statesavings.ie for a new draw —
     // surfacing it here makes it obvious from the dashboard if the cron has
     // stopped running (or stopped reaching this page) rather than only finding
     // out when results fail to show up on the site.
     $last_check_text = 'Never';
     if ($this->db->table_exists('cron_state')) {
       // Elapsed time computed inside MySQL (TIMESTAMPDIFF against its own NOW()),
       // not by comparing the stored value against PHP's time() — the two run in
       // different timezones in this environment, so that comparison would be
       // silently wrong by however many hours they disagree by.
       $row = $this->db->query(
         "SELECT TIMESTAMPDIFF(SECOND, value, NOW()) as secs_ago FROM cron_state WHERE name = 'last_live_check'"
       )->row();
       if ($row) {
         $last_check_text = get_time_ago(time() - (int) $row->secs_ago);
       }
     }
     $boxes[] = array('width' => '3', 'box_title' => 'Last Cron Check', 'box_count' => $last_check_text);

     $data["boxes"] = $boxes;

     $charts = array();
     // $charts[] = single_series_chart("Total App Downloads",'app_downloads','app_name,SUM(price)','app_name','column','Total Downloads','12');
     // $charts[] = single_series_chart("Total by Date",'app_downloads','DATE(reg) AS reg_d,COUNT(id)','reg_d','column','Total Downloads','12');
     // $charts[] = single_series_chart("Total by Date",'app_downloads',"DATE_FORMAT(reg,'%Y-%m') AS reg_d, SUM(price)",'reg_d','line','Total Money','12');

     $data["charts"] = $charts;

    //Boxes and graphs end here--------------------------------



    if (is_page_permitted('overview')) {
        $this->load->view('overview_view',$data);
    }
    else{
        $this->load->view('not_permitted');
    }
    $this->load->view('footer',$data);

	}


}
?>
