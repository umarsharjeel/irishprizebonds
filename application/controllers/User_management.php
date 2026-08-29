<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_management extends CI_Controller {


  	public function index()
  	{

  	}

    public function login()
    {

         $username = $this->input->post("username");
         $password = md5($this->input->post("password"));

         $query = $this->db->query("SELECT * from antelope_users WHERE username =? AND password=?",array($username,$password));

        if(is_null($query->row())){
          $data["error"] = true;
          $this->load->view('login',$data);
        }
        else {

          $row = $query->row_array();

          $this->session->set_userdata('antelope_user', $row);

          redirect("overview");
        }

    }

    public function logout()
    {
      $this->session->unset_userdata('antelope_user');
      redirect("welcome");
    }



}
