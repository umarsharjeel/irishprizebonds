<?php

/**
 * INSERT IGNORE for a batch of rows — lets duplicate-key rows (e.g. a bond
 * number already recorded for a draw, from a retried/overlapping fetch)
 * silently no-op instead of throwing, since draw_winners has a UNIQUE key
 * on (draw_id, bond_number). CI3's query builder insert_batch() has no
 * IGNORE option, so this builds the statement directly.
 */
function db_insert_batch_ignore($db, $table, $batch)
{
  if (empty($batch)) return;
  $columns = array_keys($batch[0]);
  $column_list = implode(',', array_map(function($c){ return "`$c`"; }, $columns));
  $value_rows = array();
  foreach ($batch as $row) {
    $escaped = array_map(function($v) use ($db) { return $v === null ? 'NULL' : $db->escape($v); }, $row);
    $value_rows[] = '(' . implode(',', $escaped) . ')';
  }
  $sql = "INSERT IGNORE INTO `$table` ($column_list) VALUES " . implode(',', $value_rows);
  $db->query($sql);
}

function antelope_config(){
  $ci=& get_instance();
  return $ci->config->item("antelope_config");
}

function is_active($current_url, $uri){
  $link_url = base_url().$uri;
  if($current_url == $link_url){
    return "active";
  }
}

function is_active_home($current_url){
  if(trim($current_url, "/") == trim(base_url(), "/")){
    return "active";
  }
}

function is_active_dropdown($current_url, $uri){
  
  if (strpos($current_url, $uri) !== false) {
    return "active";
  }
}

function get_user(){
  $ci=& get_instance();
  $user = $ci->session->userdata('antelope_user');

  //var_dump($_SESSION);


  // $query = $ci->db->get_where('antelope_users', array('id' => $user["id"]));
  // $row = $query->row_array();

  if(isset($user)){
    $user["avatar"] = base_url() . "uploads/" . $user["avatar"];

  }


  return $user;
}

function last_query(){
  $ci = & get_instance();
  echo $ci->db->last_query();
  die();
}


function get_menus(){

  $permissions = explode(',',get_user()["permissions"]);


  $ci=& get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  if (in_array('everything', $permissions)) {

      return $all_menus;
  }

  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);

    if(isset($menu["sub_menus"])){
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);

        if (!in_array($submenu_url, $permissions)) {
            unset($menu["sub_menus"][$submenukey]);
        }
      }
      if(count($menu["sub_menus"]) == 0){
          unset($all_menus[$menukey]);
      }

    }
    else{
        if (!in_array($menu_url, $permissions)) {
            unset($all_menus[$menukey]);
        }
    }
  }


  return $all_menus;

}

function get_menus_for_user_management(){

  $permissions = explode(',',get_user()["permissions"]);


  $ci=& get_instance();
  $all_menus = $ci->config->item("antelope_config")["antelope_sidebar_menus"];

  $menus_to_return = array();

  $menus_to_return["everything"] = "Everything";

$menus_to_return["my_profile"] = "My Profile";


  foreach ($all_menus as $menukey => &$menu) {

    $menu_url_array = explode('/', $menu["url"]);
    $menu_url = end($menu_url_array);


    if(isset($menu["sub_menus"])){
      foreach ($menu["sub_menus"] as $submenukey => &$submenu) {

        $submenu_url_array = explode('/', $submenu["url"]);
        $submenu_url = end($submenu_url_array);
        $menus_to_return[$menu["title"]][$submenu_url] = $submenu["title"];

      }
    }
    else{
      $menus_to_return[$menu_url] = $menu["title"];

    }
  }


  return $menus_to_return;

}


function is_page_permitted($page){

  $permissions = explode(',',get_user()["permissions"]);

  if (in_array('everything', $permissions)) {
      return true;
  }
  else{
    if (in_array($page, $permissions)) {
        return true;
    }
  }

  return false;
}


function get_time_ago( $time ){
  $time_difference = time() - $time;

  if( $time_difference < 1 ) { return 'less than 1 second ago'; }
  $condition = array( 12 * 30 * 24 * 60 * 60 =>  'year',
    30 * 24 * 60 * 60       =>  'month',
    24 * 60 * 60            =>  'day',
    60 * 60                 =>  'hour',
    60                      =>  'minute',
    1                       =>  'second'
  );

  foreach( $condition as $secs => $str )
  {
    $d = $time_difference / $secs;

    if( $d >= 1 )
    {
      $t = round( $d );
      return 'about ' . $t . ' ' . $str . ( $t > 1 ? 's' : '' ) . ' ago';
    }
  }
}
