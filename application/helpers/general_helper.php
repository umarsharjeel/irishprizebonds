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

/**
 * Prize Bond numbers are always a 2-3 letter prefix followed by exactly 6
 * digits (e.g. AHU176759, OY599204). A single old-style certificate can cover
 * a whole consecutively-numbered block of bonds, so — matching (and
 * extending) statesavings.ie's own "First / Last Bond Number" checker —
 * callers can supply a range instead of typing out every number.
 */
define('BOND_NUMBER_PATTERN', '/^([A-Z]{2,3})(\d{6})$/');

/**
 * Expands an inclusive "first, last" Prize Bond range into every individual
 * number in it. $last may be a full bond number (must share $first's letter
 * prefix) or just the trailing digits (inherits $first's prefix) — e.g.
 * expand_bond_range('AHU176720', 'AHU176759') and
 * expand_bond_range('AHU176720', '176759') both work.
 *
 * Returns array('numbers' => [...]) on success, or array('error' => '...')
 * with a human-readable reason on failure.
 */
function expand_bond_range($first, $last, $max_span = 5000)
{
  $first = strtoupper(trim($first));
  $last = strtoupper(trim($last));

  if (!preg_match(BOND_NUMBER_PATTERN, $first, $fm)) {
    return array('error' => "\"{$first}\" doesn't look like a Prize Bond number (expected 2-3 letters then 6 digits, e.g. AHU176759)");
  }
  $prefix = $fm[1];
  $width = strlen($fm[2]);

  if (preg_match('/^\d{1,' . $width . '}$/', $last)) {
    $last_num = $last;
  } elseif (preg_match(BOND_NUMBER_PATTERN, $last, $lm)) {
    if ($lm[1] !== $prefix) {
      return array('error' => "the last number's prefix ({$lm[1]}) doesn't match the first ({$prefix}) — a range has to stay within one letter prefix");
    }
    $last_num = $lm[2];
  } else {
    return array('error' => "\"{$last}\" doesn't look like a valid last bond number");
  }

  $first_int = (int) $fm[2];
  $last_int = (int) $last_num;
  if ($last_int < $first_int) {
    return array('error' => "the first number should be lower than the last");
  }

  $span = $last_int - $first_int + 1;
  if ($span > $max_span) {
    return array('error' => "that range spans " . number_format($span) . " numbers, more than the " . number_format($max_span) . " we allow in one range — try splitting it up");
  }

  $numbers = array();
  for ($n = $first_int; $n <= $last_int; $n++) {
    $numbers[] = $prefix . str_pad((string) $n, $width, '0', STR_PAD_LEFT);
  }
  return array('numbers' => $numbers);
}

/**
 * Parses Power Search's freeform "one per line, or comma/space separated"
 * input, where any token containing a hyphen (e.g. "AHU176720-176759") is
 * treated as a range and expanded via expand_bond_range() rather than a
 * literal bond number. Plain tokens pass through unchanged.
 *
 * Returns array('numbers' => [...] (deduplicated, capped at $max_total),
 * 'errors' => [...] (any range tokens that didn't parse, or a cap notice)).
 */
function expand_bond_list_with_ranges($raw_text, $max_total = 5000)
{
  $tokens = preg_split('/[\s,]+/', strtoupper(trim((string) $raw_text)));
  $tokens = array_values(array_filter($tokens, function ($t) { return $t !== ''; }));

  // Keyed by bond number rather than a plain list, so duplicates collapse as we
  // go — count($numbers) is always the true unique count. Checking the cap
  // against a pre-dedup count would let heavy duplication (e.g. the same
  // range pasted twice) trigger the cap and abandon later, genuinely-new
  // tokens even though the real unique total is nowhere near the limit.
  $numbers = array();
  $errors = array();
  $capped = false;

  foreach ($tokens as $token) {
    if (strpos($token, '-') !== false) {
      list($first, $last) = array_map('trim', explode('-', $token, 2));
      $result = expand_bond_range($first, $last, $max_total);
      if (isset($result['error'])) {
        $errors[] = "Range \"{$token}\": {$result['error']}.";
        continue;
      }
      foreach ($result['numbers'] as $n) {
        $numbers[$n] = true;
      }
    } else {
      $numbers[$token] = true;
    }

    if (count($numbers) > $max_total) {
      $capped = true;
      break;
    }
  }

  $numbers = array_keys($numbers);
  if (count($numbers) > $max_total) {
    $capped = true;
    $numbers = array_slice($numbers, 0, $max_total);
  }
  if ($capped) {
    $errors[] = "Only checked the first " . number_format($max_total) . " numbers (including expanded ranges) — that's the most we can check in one go.";
  }

  return array('numbers' => $numbers, 'errors' => $errors);
}

/**
 * True if $date_str is (or would be) the last Friday-cadence draw of its
 * calendar month — i.e. adding 7 days crosses into the next month. Used as
 * a jackpot guess: for real draw rows it's only ever provisional (Cron
 * corrects it from the actual discovered prize tiers once known); for the
 * Schedule page's calendar-projected upcoming dates it's the best guess
 * available since those dates aren't confirmed draws at all.
 */
function is_last_friday_of_month($date_str)
{
  $d = new DateTime($date_str);
  $next = clone $d;
  $next->modify('+7 days');
  return $d->format('n') !== $next->format('n');
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
