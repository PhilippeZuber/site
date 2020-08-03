<?php

session_start();

require_once('system/data.php');
require_once('system/security.php');

$totaldata = get_result("select count(*) count from words ");
$totaldata = mysqli_fetch_assoc($totaldata);


$wh = " where 1=1 ";
if ($_REQUEST['search_text'] != '') {



    if (substr($_REQUEST['search_text'], -1) == '*' && substr($_REQUEST['search_text'], 0, 1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '%_" . str_replace('*', '', $_REQUEST['search_text']) . "_%' )";
    } else if (substr($_REQUEST['search_text'], -1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '" . str_replace('*', '', $_REQUEST['search_text']) . "%' )";
    } else if (substr($_REQUEST['search_text'], 0, 1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '%" . str_replace('*', '', $_REQUEST['search_text']) . "' )";
    } else {
        $wh .= " and (  ";
        $wh .= "  name like '%" . str_replace('*', '', $_REQUEST['search_text']) . "%' )";
    }

//    $wh .= "  name REGEXP  '" . $_REQUEST['search_text'] . "' )";
}

if ($_REQUEST['not_letter'] != '') {
        $wh = "test"
}

if (!empty($_REQUEST['category'])) {
    $wh .= "  and category in ('" . implode("','", $_REQUEST['category']) . "')";
}
if (!empty($_REQUEST['semantic'])) {
    $wh .= "  and ( ";
    foreach ($_REQUEST['semantic'] as $key_s => $value_s) {
        if ($key_s != 0) {
            $wh .= "  or ";
        }
        $wh .= "   (
             semantic like '%," . $value_s . ",%' or
             semantic like '%," . $value_s . "%' or
             semantic like '%" . $value_s . ",%' or
             semantic = '" . $value_s . "' 
            )";
    }
    $wh .= "  ) ";
}
if (!empty($_REQUEST['alter'])) {
    $wh .= "  and alters in ('" . implode("','", $_REQUEST['alter']) . "')";
}
if ($_REQUEST['lauttreu'] != 'false') {
    $wh .= "  and lauttreu = 1";
}



$data3 = get_result("select count(*) as count from words $wh ");
$data3 = mysqli_fetch_assoc($data3);
$order_by = " Order by " . ($_REQUEST['order'][0]['column'] + 1) . " " . $_REQUEST['order'][0]['dir'];

/*$data = get_result("select name,image_url  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);*///freepik
/*$data = get_result("select name,image  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);*/
$data = get_result("select name,image, image_url  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);
$i = 0;
$data2 = array();
while ($row = mysqli_fetch_array($data)) {

    $data2[$i][0] = $row['name'];

    if ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] != '') {
        $data2[$i][1] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
        $data2[$i][2] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] == '') { 
        $data2[$i][1] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
        $data2[$i][2] = '';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] == '' && $row['image_url'] != '') { 
        $data2[$i][1] = '';
        $data2[$i][2] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    }else {
        $data2[$i][1] = '';
        $data2[$i][2] = '';
    }

    $i++;
}

$json_data = array(
    "recordsTotal" => intval($totaldata['count']),
    "recordsFiltered" => intval($data3['count']),
    "data" => $data2
);
echo json_encode($json_data);
