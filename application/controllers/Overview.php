<?php
class Overview extends CI_Controller {

	 function __construct()
    {
      parent::__construct();

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
