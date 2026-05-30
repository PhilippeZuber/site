<?php

session_start();

require_once('system/data.php');
require_once('system/security.php');

$include_selection = isset($_REQUEST['include_selection']) ? filter_var($_REQUEST['include_selection'], FILTER_VALIDATE_BOOLEAN) : false;
$image_mode = (isset($_REQUEST['image_mode']) && $_REQUEST['image_mode'] === 'ausmalbild') ? 'ausmalbild' : 'standard';
$search_source = isset($_REQUEST['source_page']) ? filter_data($_REQUEST['source_page']) : 'unknown';

$search_text_value = isset($_REQUEST['search_text']) ? $_REQUEST['search_text'] : '';
$not_letter_value = isset($_REQUEST['not_letter']) ? $_REQUEST['not_letter'] : '';
$category_values = !empty($_REQUEST['category']) && is_array($_REQUEST['category']) ? array_map('intval', $_REQUEST['category']) : array();
$semantic_values = !empty($_REQUEST['semantic']) && is_array($_REQUEST['semantic']) ? array_map('intval', $_REQUEST['semantic']) : array();
$alter_values = !empty($_REQUEST['alter']) && is_array($_REQUEST['alter']) ? array_map('intval', $_REQUEST['alter']) : array();
$lauttreu_value = isset($_REQUEST['lauttreu']) && $_REQUEST['lauttreu'] != 'false';

$totaldata = get_result("select count(*) count from words ");
$totaldata = mysqli_fetch_assoc($totaldata);

$wh = " where 1=1 ";
if ($search_text_value != '') {
    if (substr($search_text_value, -1) == '*' && substr($search_text_value, 0, 1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '%_" . str_replace('*', '', $search_text_value) . "_%' )";
    } else if (substr($search_text_value, -1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '" . str_replace('*', '', $search_text_value) . "%' )";
    } else if (substr($search_text_value, 0, 1) == '*') {
        $wh .= " and (  ";
        $wh .= "  name like '%" . str_replace('*', '', $search_text_value) . "' )";
    } else {
        $wh .= " and (  ";
        $wh .= "  name like '%" . str_replace('*', '', $search_text_value) . "%' )";
    }
}

if ($not_letter_value != '') {
    $wh .= " and ( name not like '%" . $not_letter_value . "%')";
}

if (!empty($category_values)) {
    $wh .= "  and category in ('" . implode("','", $category_values) . "')";
}
if (!empty($semantic_values)) {
    $wh .= "  and ( ";
    foreach ($semantic_values as $key_s => $value_s) {
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
if (!empty($alter_values)) {
    $wh .= "  and alters in ('" . implode("','", $alter_values) . "')";
}
if ($lauttreu_value) {
    $wh .= "  and lauttreu = 1";
}

$data3 = get_result("select count(*) as count from words $wh ");
$data3 = mysqli_fetch_assoc($data3);

log_search_request(
    isset($_SESSION['id']) ? (int) $_SESSION['id'] : null,
    $search_source,
    $search_text_value,
    $not_letter_value,
    implode(',', $category_values),
    implode(',', $semantic_values),
    implode(',', $alter_values),
    $lauttreu_value,
    isset($data3['count']) ? (int) $data3['count'] : 0
);

$order_column = isset($_REQUEST['order'][0]['column']) ? intval($_REQUEST['order'][0]['column']) : ($include_selection ? 1 : 0);
$order_dir = isset($_REQUEST['order'][0]['dir']) && $_REQUEST['order'][0]['dir'] == 'desc' ? 'desc' : 'asc';

if ($include_selection) {
    $order_columns = array(
        0 => 'name',
        1 => 'name',
        2 => 'image',
        3 => 'image_url'
    );
} else {
    $order_columns = array(
        0 => 'name',
        1 => 'image',
        2 => 'image_url'
    );
}

$order_by = " Order by " . (isset($order_columns[$order_column]) ? $order_columns[$order_column] : 'name') . " " . $order_dir;

$start = isset($_REQUEST['start']) ? (int) $_REQUEST['start'] : 0;
$length = isset($_REQUEST['length']) ? (int) $_REQUEST['length'] : 10;
$data = get_result("select id, name, image, image_ausmalbild, image_url from words $wh $order_by limit " . $start . "," . $length);

$i = 0;
$data2 = array();
while ($row = mysqli_fetch_array($data)) {
    $column_index = 0;

    if ($include_selection) {
        $data2[$i][$column_index++] = '<input type="checkbox" class="memory-select" value="' . $row['id'] . '">';
    }

    $data2[$i][$column_index++] = $row['name'];

    if ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] != '') {
        $img_src = ($image_mode === 'ausmalbild' && $row['image_ausmalbild'] != '') ? $row['image_ausmalbild'] : $row['image'];
        $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $img_src . '">';
        $data2[$i][$column_index++] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] == '') {
        $img_src = ($image_mode === 'ausmalbild' && $row['image_ausmalbild'] != '') ? $row['image_ausmalbild'] : $row['image'];
        $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $img_src . '">';
        $data2[$i][$column_index++] = '';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] == '' && $row['image_url'] != '') {
        $data2[$i][$column_index++] = '';
        $data2[$i][$column_index++] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    } else {
        $data2[$i][$column_index++] = '';
        $data2[$i][$column_index++] = '';
    }

    $i++;
}

$json_data = array(
    "recordsTotal" => intval($totaldata['count']),
    "recordsFiltered" => intval($data3['count']),
    "data" => $data2
);
echo json_encode($json_data);
