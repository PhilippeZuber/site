<?php

session_start();

require_once('system/data.php');
require_once('system/security.php');

$include_selection = isset($_REQUEST['include_selection']) ? filter_var($_REQUEST['include_selection'], FILTER_VALIDATE_BOOLEAN) : false;
$minimalpair_enabled = isset($_REQUEST['minimalpair_enabled']) ? filter_var($_REQUEST['minimalpair_enabled'], FILTER_VALIDATE_BOOLEAN) : false;
$minimalpair_base = isset($_REQUEST['minimalpair_base']) ? trim($_REQUEST['minimalpair_base']) : '';

function split_word_chars($word)
{
    $chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
    if ($chars === false) {
        $chars = str_split($word);
    }
    return $chars;
}

function normalize_word_for_compare($word)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($word, 'UTF-8');
    }

    return strtolower($word);
}

function get_strict_minimalpair_difference($base_word, $candidate_word)
{
    $base_chars = split_word_chars($base_word);
    $candidate_chars = split_word_chars($candidate_word);

    if (count($base_chars) !== count($candidate_chars)) {
        return false;
    }

    $diff_count = 0;
    $diff_from = '';
    $diff_to = '';
    $diff_position = 0;

    foreach ($base_chars as $index => $char) {
        if ($char !== $candidate_chars[$index]) {
            $diff_count++;
            $diff_from = $char;
            $diff_to = $candidate_chars[$index];
            $diff_position = $index + 1;

            if ($diff_count > 1) {
                return false;
            }
        }
    }

    if ($diff_count !== 1) {
        return false;
    }

    return array(
        'from' => $diff_from,
        'to' => $diff_to,
        'position' => $diff_position
    );
}

function compare_minimalpair_rows($left, $right, $order_column, $order_dir)
{
    $left_value = '';
    $right_value = '';

    if ($order_column === 2) {
        $left_value = $left['pair_word'];
        $right_value = $right['pair_word'];
    } elseif ($order_column === 3) {
        $left_value = $left['difference'];
        $right_value = $right['difference'];
    } else {
        $left_value = $left['pair_word'];
        $right_value = $right['pair_word'];
    }

    $compare = strcasecmp($left_value, $right_value);
    if ($compare === 0) {
        $compare = $left['id'] - $right['id'];
    }

    if ($order_dir === 'desc') {
        return -$compare;
    }

    return $compare;
}

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
        $wh .= " and ( name not like '%" . $_REQUEST['not_letter'] . "%')";
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

$order_column = isset($_REQUEST['order'][0]['column']) ? intval($_REQUEST['order'][0]['column']) : ($include_selection ? 1 : 0);
$order_dir = isset($_REQUEST['order'][0]['dir']) && $_REQUEST['order'][0]['dir'] == 'desc' ? 'desc' : 'asc';

if ($include_selection) {
    $order_columns = array(
        0 => 'name',
        1 => 'name',
        2 => 'name',
        3 => 'name',
        4 => 'image',
        5 => 'image_url'
    );
} else {
    $order_columns = array(
        0 => 'name',
        1 => 'name',
        2 => 'name',
        3 => 'image',
        4 => 'image_url'
    );
}

$order_by = " Order by " . (isset($order_columns[$order_column]) ? $order_columns[$order_column] : 'name') . " " . $order_dir;

/*$data = get_result("select name,image_url  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);*///freepik
/*$data = get_result("select name,image  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);*/

$data = false;
$minimalpair_rows = array();
$records_filtered = intval($data3['count']);

if ($minimalpair_enabled && $minimalpair_base !== '') {
    $base_word_normalized = normalize_word_for_compare($minimalpair_base);
    $all_filtered_words = get_result("select id, name, image, image_url from words $wh");

    while ($row = mysqli_fetch_array($all_filtered_words)) {
        $candidate_name = $row['name'];
        $candidate_normalized = normalize_word_for_compare($candidate_name);

        if ($candidate_normalized === $base_word_normalized) {
            continue;
        }

        $difference = get_strict_minimalpair_difference($minimalpair_base, $candidate_name);
        if ($difference === false) {
            continue;
        }

        $minimalpair_rows[] = array(
            'id' => $row['id'],
            'pair_word' => $candidate_name,
            'difference' => $difference['from'] . '→' . $difference['to'] . ' (Pos. ' . $difference['position'] . ')',
            'image' => $row['image'],
            'image_url' => $row['image_url']
        );
    }

    usort($minimalpair_rows, function ($left, $right) use ($order_column, $order_dir) {
        return compare_minimalpair_rows($left, $right, $order_column, $order_dir);
    });

    $records_filtered = count($minimalpair_rows);
    $data = array_slice($minimalpair_rows, intval($_REQUEST['start']), intval($_REQUEST['length']));
} else {
    $data = get_result("select id, name, image, image_url  from words $wh $order_by limit " . $_REQUEST['start'] . "," . $_REQUEST['length']);
}

$i = 0;
$data2 = array();
if ($minimalpair_enabled && $minimalpair_base !== '') {
    foreach ($data as $row) {
        $column_index = 0;

        if ($include_selection) {
            $data2[$i][$column_index++] = '<input type="checkbox" class="memory-select" value="' . $row['id'] . '">';
        }

        $data2[$i][$column_index++] = $minimalpair_base;
        $data2[$i][$column_index++] = $row['pair_word'];
        $data2[$i][$column_index++] = $row['difference'];

        if ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] != '') {
            $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
            $data2[$i][$column_index++] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
        } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] == '') {
            $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
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
} else {
while ($row = mysqli_fetch_array($data)) {

    $column_index = 0;

    if ($include_selection) {
        $data2[$i][$column_index++] = '<input type="checkbox" class="memory-select" value="' . $row['id'] . '">';
    }

    $data2[$i][$column_index++] = $row['name'];
    $data2[$i][$column_index++] = '';
    $data2[$i][$column_index++] = '';

    if ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] != '') {
        $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
        $data2[$i][$column_index++] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] != '' && $row['image_url'] == '') { 
        $data2[$i][$column_index++] = '<img style="width:150px;" src="images/' . $row['image'] . '">';
        $data2[$i][$column_index++] = '';
    } elseif ($_REQUEST['search_image'] != 'false' && $row['image'] == '' && $row['image_url'] != '') { 
        $data2[$i][$column_index++] = '';
        $data2[$i][$column_index++] = '<img style="width:150px;" src="' . $row['image_url'] . '">';
    }else {
        $data2[$i][$column_index++] = '';
        $data2[$i][$column_index++] = '';
    }

    $i++;
}
}

$json_data = array(
    "recordsTotal" => intval($totaldata['count']),
    "recordsFiltered" => $records_filtered,
    "data" => $data2
);
echo json_encode($json_data);
