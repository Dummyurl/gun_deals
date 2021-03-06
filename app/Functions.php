<?php

/**
 * Website General Functions
 *
 */

function getParents($id)
{
    $ids = [];

    $row = \DB::table("product_categories")
    ->where("id",$id)
    ->first();

    
    if($row->parent_id > 0)
    {
        $ids[] = $row->parent_id;
        $newIDS = getParents($row->parent_id);
        $ids = array_merge($ids,$newIDS);
    }    

    return $ids;
}

function getChildrens($id)
{
    $ids = [];

    $rows = \DB::table("product_categories")
    ->where("parent_id",$id)
    ->get();

    foreach($rows as $row)
    {
        $ids[] = $row->id;
        $newIDS = getChildrens($row->id);
        $ids = array_merge($ids,$newIDS);
    }

    return $ids;
}

function display_children($parent, $level) 
{
    if($parent > 0)
    {
        $sql = "SELECT a.id, a.title, Deriv1.Count,a.url FROM `product_categories` a  
    LEFT OUTER JOIN (SELECT parent_id, COUNT(*) AS Count FROM `product_categories` GROUP BY parent_id) Deriv1 ON a.id = Deriv1.parent_id 
    WHERE a.parent_id=".$parent;
    }
    else
    {
        $sql = "SELECT a.id, a.title, Deriv1.Count,a.url FROM `product_categories` a  
    LEFT OUTER JOIN (SELECT parent_id, COUNT(*) AS Count FROM `product_categories` GROUP BY parent_id) Deriv1 ON a.id = Deriv1.parent_id 
    WHERE a.parent_id IS NULL";
    }

    $result = \DB::select($sql);
    $result = json_decode(json_encode($result),1);

    echo "<ul>";

    foreach($result as $row)
    {        
        if ($row['Count'] > 0) 
        {
            echo "<li><a href='/" . $row['url'] . "'>" . $row['title'] . "</a>";
            display_children($row['id'], $level + 1);
            echo "</li>";
        } elseif ($row['Count']==0) {
            echo "<li><a href='/" . $row['url'] . "'>" . $row['title'] . "</a></li>";
        } else;
    }

    echo "</ul>";
}

function storeCronLogs($start_time, $end_time, $total_time, $content, $machine_id, $cron_id, $insertedID = 0) 
{
    if($insertedID > 0)
    {
        $dataToUpdate = array
        (
            'end_time' => date("Y-m-d H:i:s", strtotime($end_time)),
            'summary' => json_encode($content),
        );

        \App\Models\CronLogDetail::where("id", $insertedID)->update($dataToUpdate);
    }
    else
    {
        $dataToUpdate = array
        (
            'cron_log_id' => $cron_id,
            'start_time' => date("Y-m-d H:i:s", strtotime($start_time)),
            'end_time' => NULL,
            'machine_id' => $machine_id            
        );        

        $model = new \App\Models\CronLogDetail;
        $model = $model->create($dataToUpdate);        
        return $model->id;
    }        
}    

function getFilename($fullpath, $uploaded_filename) {
    $count = 1;
    $new_filename = $uploaded_filename;
    $firstinfo = pathinfo($fullpath);

    while (file_exists($fullpath)) {
        $info = pathinfo($fullpath);
        $count++;
        $new_filename = $firstinfo['filename'] . '(' . $count . ')' . '.' . $info['extension'];
        $fullpath = $info['dirname'] . '/' . $new_filename;
    }

    return $new_filename;
}

function humanTiming($time) {

    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1) ? 1 : $time;

    $tokens = array(
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit)
            continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '');
    }
}

function removeDir($dir) {
    foreach (glob($dir . "/*.*") as $filename) {
        if (is_file($filename)) {
            unlink($filename);
        }
    }

    if (is_dir($dir . "feature")) {

        foreach (glob($dir . "feature/*.*") as $filename) {
            if (is_file($filename)) {
                unlink($filename);
            }
        }

        rmdir($dir . "feature");
    }

    rmdir($dir);
}

function sendHtmlMail($params) {
    $files = isset($params['files']) ? $params['files'] : array();

    \Mail::send('emails.index', $params, function($message) use ($params, $files) {
        $message->to($params['to'], '')->subject($params['subject']);

        if (count($files) > 0) {
            foreach ($files as $file) {
                $message->attach($file['path']);
            }
        }
    });
}

// to generate random string
function getRandomString($len = 30) {
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

    //$chars = "0123456789";
    $r_str = "";
    for ($i = 0; $i < $len; $i++)
        $r_str .= substr($chars, rand(0, strlen($chars)), 1);

    if (strlen($r_str) != $len) {
        $r_str .= getRandomString($len - strlen($r_str));
    }

    return $r_str;
}

// to generate random string number
function getRandomStringNumber($len = 30) {
    // $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $chars = "0123456789";
    $r_str = "";
    for ($i = 0; $i < $len; $i++)
        $r_str .= substr($chars, rand(0, strlen($chars)), 1);

    if (strlen($r_str) != $len) {
        $r_str .= getRandomStringNumber($len - strlen($r_str));
    }

    return $r_str;
}

// for table heading sorting link
function getSortingLink($link, $heading, $field, $curSortBy = '', $curSortOrder = 'asc', $search_field = '', $search_val = '', $extra_params = '') {

    $qs = '?';
    if (strpos($link, '?') != false) {
        $qs = '&';
    }



    if ($field != $curSortBy) {
        $link .= $qs . 'sortBy=' . $field . '&sortOrd=asc';
    } elseif ($field == $curSortBy) {
        if ($curSortOrder == "asc") {
            $link .= $qs . 'sortBy=' . $field . '&sortOrd=desc';
        } elseif ($curSortOrder == "desc") {
            $link .= $qs . 'sortBy=' . $field . '&sortOrd=asc';
        } else {
            $link .= $qs . 'sortBy=' . $field . '&sortOrd=asc';
        }
    }

    if ($search_field != "" && $search_val != "") {
        $link .= '&search_field=' . $search_field . "&search_text=" . $search_val;
    }

    if ($extra_params != "") {
        $link .= "&" . $extra_params;
    }


    return '<a href="' . $link . '">' . $heading . '</a>';
}

function dateFormat($date, $format = '', $withTime = false) {


    if ($date == "0000-00-00 00:00:00" || $date == "0000-00-00" || $date == "0000-00-00 00:00:00000000" || $date == "" || is_null($date)) {
        return '-';
    }

    $temp = '';
    if ($withTime) {
        $temp = ' H:i a';
    }

    if ($format == '') {
        return date(env('APP_DATE_FORMAT', 'Y-m-d') . $temp, strtotime($date));
    } else {
        return date($format, strtotime($date));
    }
}

function downloadFile($filename, $filepath) {
    $fsize = filesize($filepath);
    header('Pragma: public');
    header('Cache-Control: public, no-cache');
    header('Content-Type: ' . mime_content_type($filepath));
    header('Content-Length: ' . $fsize);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    readfile($filepath);
    exit;
}

function isDigits($quantity) {
    return preg_match("/[^0-9]/", $quantity);
}

function displayPrice($price, $with_symbol = 1) {
    if ($with_symbol == 1)
        return "$" . number_format($price, 2);
    else
        return number_format($price, 2);
}

function makeDir($path) {
    if (!is_dir($path)) {
        mkdir($path);
        chmod($path, 0777);
    }
}

function get_month_name($month) {
    $months = array(
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    );

    return $months[$month];
}

function NVPToArray($NVPString) {
    $proArray = array();

    while (strlen($NVPString)) {
        // name
        $keypos = strpos($NVPString, '=');
        $keyval = substr($NVPString, 0, $keypos);
        // value
        $valuepos = strpos($NVPString, '&') ? strpos($NVPString, '&') : strlen($NVPString);
        $valval = substr($NVPString, $keypos + 1, $valuepos - $keypos - 1);
        // decoding the respose
        $proArray[$keyval] = urldecode($valval);
        $NVPString = substr($NVPString, $valuepos + 1, strlen($NVPString));
    }

    return $proArray;
}

/**
 * Website General Model Functions
 *
 */
function getRecordsFromSQL($sql, $returnType = "array") {
    $result = \DB::select($sql);

    if ($returnType == "array") {
        $result = json_decode(json_encode($result), true);
    } else {
        return $result;
    }
}

function getRecords($table, $whereArr, $returnType = "array") {
    $result = \DB::table($table)->from($table);

    if (is_array($whereArr) && count($whereArr) > 0) {
        foreach ($whereArr as $field => $value) {
            $result->where($field, $value);
        }
    }

    $result = $result->get();


    if ($returnType == "array") {
        $result = json_decode(json_encode($result), true);
    } else {
        return $result;
    }
}

function GetUserIp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';

    /* If Local IP */
    if ($ipaddress == "UNKNOWN" || $ipaddress == "127.0.0.1")
        $ipaddress = '72.229.28.185'; //NY

        /* if($ipaddress == '203.88.138.46') { //GJ
          $ipaddress = '128.101.101.101'; //MN
          $ipaddress = '24.128.151.64'; //Adrian
          $ipaddress = '66.249.69.245'; //CA
          $ipaddress = '72.229.28.185'; //NY
          $ipaddress = '127.0.0.1'; //UNKNOWN
          $ipaddress = '2603:300a:f05:a000:2970:d9ff:9da:ccd6'; //Patrick mobile IPv6
          } */

    return $ipaddress;
}

function getAdminUserTypes()
{
    $array = array();
    
    $rows = \DB::table("admin_user_types")->get();

    foreach($rows as $row)
    {
        $array[$row->id] = $row->title;    
    }    

    return $array;
}

function filterPrice($price)
{
    $price = trim($price);
    $price = str_replace("$", "", $price);
    $price = str_replace(",", "", $price);
    $price = trim($price);
    $price = floatval($price);    
    return $price;
}

function getBreadCrumbArr($categoryID, $lastArr = [])
{
    $arr = [];
    $obj = \App\Models\Category::find($categoryID);
    if($obj)
    {
        $arr[] = $obj->title;
        
        if($obj->parent_id > 0)
        $arr = getBreadCrumbArr($obj->parent_id, $arr);
    }
    
    return array_merge($arr,$lastArr);
}
