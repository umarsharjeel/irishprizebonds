<?php
defined('BASEPATH') OR exit('No direct script access alloe');

$antelope_config_json  = $str = file_get_contents('antelope_config.json');

$antelope_config = json_decode($antelope_config_json,true);

?>
