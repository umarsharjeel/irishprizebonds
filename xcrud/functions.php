<?php
function publish_action($xcrud)
{
    if ($xcrud->get('primary'))
    {
        $db = Xcrud_db::get_instance();
        $query = 'UPDATE base_fields SET `bool` = b\'1\' WHERE id = ' . (int)$xcrud->get('primary');
        $db->query($query);
    }
}
function unpublish_action($xcrud)
{
    if ($xcrud->get('primary'))
    {
        $db = Xcrud_db::get_instance();
        $query = 'UPDATE base_fields SET `bool` = b\'0\' WHERE id = ' . (int)$xcrud->get('primary');
        $db->query($query);
    }
}

function exception_example($postdata, $primary, $xcrud)
{
    // get random field from $postdata
    $postdata_prepared = array_keys($postdata->to_array());
    shuffle($postdata_prepared);
    $random_field = array_shift($postdata_prepared);
    // set error message
    $xcrud->set_exception($random_field, 'This is a test error', 'error');
}

function test_column_callback($value, $fieldname, $primary, $row, $xcrud)
{
    return $value . ' - nice!';
}

function after_upload_example($field, $file_name, $file_path, $params, $xcrud)
{
    $ext = trim(strtolower(strrchr($file_name, '.')), '.');
    if ($ext != 'pdf' && $field == 'uploads.simple_upload')
    {
        unlink($file_path);
        $xcrud->set_exception('simple_upload', 'This is not PDF', 'error');
    }
}

function movetop($xcrud)
{
    if ($xcrud->get('primary') !== false)
    {
        $primary = (int)$xcrud->get('primary');
        $db = Xcrud_db::get_instance();
        $query = 'SELECT `officeCode` FROM `offices` ORDER BY `ordering`,`officeCode`';
        $db->query($query);
        $result = $db->result();
        $count = count($result);

        $sort = array();
        foreach ($result as $key => $item)
        {
            if ($item['officeCode'] == $primary && $key != 0)
            {
                array_splice($result, $key - 1, 0, array($item));
                unset($result[$key + 1]);
                break;
            }
        }

        foreach ($result as $key => $item)
        {
            $query = 'UPDATE `offices` SET `ordering` = ' . $key . ' WHERE officeCode = ' . $item['officeCode'];
            $db->query($query);
        }
    }
}
function movebottom($xcrud)
{
    if ($xcrud->get('primary') !== false)
    {
        $primary = (int)$xcrud->get('primary');
        $db = Xcrud_db::get_instance();
        $query = 'SELECT `officeCode` FROM `offices` ORDER BY `ordering`,`officeCode`';
        $db->query($query);
        $result = $db->result();
        $count = count($result);

        $sort = array();
        foreach ($result as $key => $item)
        {
            if ($item['officeCode'] == $primary && $key != $count - 1)
            {
                unset($result[$key]);
                array_splice($result, $key + 1, 0, array($item));
                break;
            }
        }

        foreach ($result as $key => $item)
        {
            $query = 'UPDATE `offices` SET `ordering` = ' . $key . ' WHERE officeCode = ' . $item['officeCode'];
            $db->query($query);
        }
    }
}

function show_description($value, $fieldname, $primary_key, $row, $xcrud)
{
    $result = '';
    if ($value == '1')
    {
        $result = '<i class="fa fa-check" />' . 'OK';
    }
    elseif ($value == '2')
    {
        $result = '<i class="fa fa-circle-o" />' . 'Pending';
    }
    return $result;
}

function custom_field($value, $fieldname, $primary_key, $row, $xcrud)
{
    return '<input type="text" readonly class="xcrud-input" name="' . $xcrud->fieldname_encode($fieldname) . '" value="' . $value .
    '" />';
}
function unset_val($postdata)
{
    $postdata->del('Paid');
}

function format_phone($new_phone)
{
    $new_phone = preg_replace("/[^0-9]/", "", $new_phone);

    if (strlen($new_phone) == 7)
        return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $new_phone);
    elseif (strlen($new_phone) == 10)
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $new_phone);
    else
        return $new_phone;
}

function before_list_example($list, $xcrud)
{
    var_dump($list);
}

function after_update_test($pd, $pm, $xc)
{
    $xc->search = 0;
}


function add_draw($postdata, $xcrud)
{
    $draw_no = $postdata->get('draw_no');
    $bond_id = $postdata->get('bond_id');
    $city_id = $postdata->get('city_id');
    $date = $postdata->get('date');
    $db = Xcrud_db::get_instance();
    $date_slug = strtolower((new DateTime($date))->format('d-F-Y'));
    $query = "select city from cities where id = " . $city_id ;
    $db->query($query);
    $city_slug = strtolower($db->row()['city']);

    $slug = "draw-".$draw_no."-held-on-".$date_slug."-at-".$city_slug."-list";

    $query = "insert into draws(draw_no,bond_id,city_id,date,slug) values(".$draw_no.",".$bond_id.",".$city_id.",'".$date."','".$slug."')";
    $db->query($query);

}

function insert_result($postdata, $xcrud)
{
    $draw_id = $postdata->get('draw_id');
    $first_number = $postdata->get('first_number');
    $second_numbers = $postdata->get('second_numbers');
    $third_numbers = $postdata->get('third_numbers');
    $second_prize = $postdata->get('second_prize');
    $third_prize = $postdata->get('third_prize');
    $db = Xcrud_db::get_instance();
    $query = "insert into results(draw_id,first_number,second_numbers,third_numbers,second_prize,third_prize) values(".$draw_id.",'".$first_number."','".$second_numbers."','".$third_numbers."','".$second_prize."','".$third_prize."')";
    $db->query($query);
    $query = "update draws set result_updated='Y' where id =".$draw_id;
    $db->query($query);
}

function delete_result($primary_key, $xcrud)
{
    $db = Xcrud_db::get_instance();
    $query = "select draw_id from results where id = ".$primary_key;
    $db->query($query);
    $draw_id = $db->row()['draw_id'];
    $query = "update draws set result_updated='N' where id =".$draw_id;
    $db->query($query);
    $query = "delete from results where id = ".$primary_key;
    $db->query($query);
}
function format_data($value, $fieldname, $primary_key, $row, $xcrud)
{

    $numbers = explode(',', $value);

    // Split into groups of 17
    $chunks = array_chunk($numbers, 17);
    
    // Join each group with tabs
    $lines = array_map(function ($chunk) {
        return implode('&#09;', $chunk);
    }, $chunks);
    
    // Join groups with a line break
    return implode('<br>', $lines);    
}

function format_post($value, $fieldname, $primary_key, $row, $xcrud)
{

    $db = Xcrud_db::get_instance();
    $query = 'SELECT name,draw_no,date_format(date,"%d %M %Y") as date,city,first_number,second_numbers,slug,url FROM results r inner join draws d on r.draw_id = d.id inner join bonds b on b.id = d.bond_id inner join cities c on c.id = d.city_id where draw_id = '.$value.' order by r.id desc';
    $db->query($query);
    $result = $db->row();

    $text = $result['name'].' Prize Bond Draw No. '.$result['draw_no'].' ('.$result['date'].', '.$result['city'].') Result</br>
First: '.$result['first_number'].'</br>
Second: '.str_replace(',',' ',$result['second_numbers']).'</br>
Full list: https://www.pakprizebond.net/'.$result['url']."/".$result['slug'].'/';
 return $text;    
}

function check_data($postdata, $xcrud){
    $db = Xcrud_db::get_instance();
    $draw_id = $postdata->get('draw_id');

    $first_number = $postdata->get('first_number');
    $second_numbers = $postdata->get('second_numbers');
    $third_numbers = $postdata->get('third_numbers');

    $first_number = preg_replace('/[^0-9]/','',$first_number);
    $first_number = str_split($first_number,6);
    sort($first_number);
    $first_number = implode(",",$first_number);

    $second_numbers = preg_replace('/[^0-9]/','',$second_numbers);
    $second_numbers = str_split($second_numbers,6);
    sort($second_numbers);
    $second_numbers = implode(",",$second_numbers);

    $third_numbers = preg_replace('/[^0-9]/','',$third_numbers);
    $third_numbers = str_split($third_numbers,6);
    $exclude = array('000000');
    $third_numbers = array_diff($third_numbers, $exclude);
    sort($third_numbers);
    $third_numbers = implode(",",$third_numbers);
    
    if(!preg_match('/^\d{6}(,\d{6})*$/', $first_number)){
        $xcrud->set_exception('first_number','First number\'s format is not correct!');
    }
    elseif(!preg_match('/^\d{6}(,\d{6})*$/', $second_numbers)){
        $xcrud->set_exception('second_numbers','Second numbers\' format is not correct!');
    }
    $postdata->set('first_number',$first_number);
    $postdata->set('second_numbers',$second_numbers);
    $postdata->set('third_numbers',$third_numbers);
    $postdata->set('second_prize',count(explode(',',$second_numbers)));
    $postdata->set('third_prize',count(explode(',',$third_numbers)));
}

function check_data_update($postdata,$primary, $xcrud){
    $db = Xcrud_db::get_instance();

    $first_number = $postdata->get('first_number');
    $second_numbers = $postdata->get('second_numbers');
    $third_numbers = $postdata->get('third_numbers');

    $first_number = preg_replace('/[^0-9]/','',$first_number);
    $first_number = str_split($first_number,6);
    sort($first_number);
    $first_number = implode(",",$first_number);

    $second_numbers = preg_replace('/[^0-9]/','',$second_numbers);
    $second_numbers = str_split($second_numbers,6);
    sort($second_numbers);
    $second_numbers = implode(",",$second_numbers);

    $third_numbers = preg_replace('/[^0-9]/','',$third_numbers);
    $third_numbers = str_split($third_numbers,6);
    $exclude = array('000000');
    $third_numbers = array_diff($third_numbers, $exclude);
    sort($third_numbers);
    $third_numbers = implode(",",$third_numbers);
    
    
    if(!preg_match('/^\d{6}(,\d{6})*$/', $first_number)){
        $xcrud->set_exception('first_number','First number\'s format is not correct!');
    }
    elseif(!preg_match('/^\d{6}(,\d{6})*$/', $second_numbers)){
        $xcrud->set_exception('second_numbers','Second numbers\' format is not correct!');
    }
    

    $postdata->set('first_number',$first_number);
    $postdata->set('second_numbers',$second_numbers);
    $postdata->set('third_numbers',$third_numbers);
    $postdata->set('second_prize',count(explode(',',$second_numbers)));
    $postdata->set('third_prize',count(explode(',',$third_numbers)));
}


