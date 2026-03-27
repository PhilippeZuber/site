<?php

session_start();

require_once('system/data.php');
require_once('system/security.php');

$include_selection = isset($_REQUEST['include_selection']) ? filter_var($_REQUEST['include_selection'], FILTER_VALIDATE_BOOLEAN) : false;
$minimalpair_enabled = isset($_REQUEST['minimalpair_enabled']) ? filter_var($_REQUEST['minimalpair_enabled'], FILTER_VALIDATE_BOOLEAN) : false;
$minimalpair_from_input = isset($_REQUEST['minimalpair_from']) ? trim($_REQUEST['minimalpair_from']) : '';
$minimalpair_to_input = isset($_REQUEST['minimalpair_to']) ? trim($_REQUEST['minimalpair_to']) : '';
$minimalpair_bidirectional = isset($_REQUEST['minimalpair_bidirectional']) ? filter_var($_REQUEST['minimalpair_bidirectional'], FILTER_VALIDATE_BOOLEAN) : true;

function normalize_word_for_compare($word)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($word, 'UTF-8');
    }

    return strtolower($word);
}

function uppercase_word_for_display($word)
{
    if (function_exists('mb_strtoupper')) {
        return mb_strtoupper($word, 'UTF-8');
    }

    return strtoupper($word);
}

function find_all_occurrences($haystack, $needle)
{
    $positions = array();
    $needle_length = strlen($needle);

    if ($needle === '' || $needle_length === 0) {
        return $positions;
    }

    $offset = 0;
    while (($position = strpos($haystack, $needle, $offset)) !== false) {
        $positions[] = $position;
        $offset = $position + 1;
    }

    return $positions;
}

function compare_minimalpair_rows($left, $right, $order_column, $order_dir)
{
    $left_value = '';
    $right_value = '';

    if ($order_column === 1) {
        $left_value = $left['word'];
        $right_value = $right['word'];
    } elseif ($order_column === 2) {
        $left_value = $left['pair_word'];
        $right_value = $right['pair_word'];
    } elseif ($order_column === 3) {
        $left_value = $left['difference'];
        $right_value = $right['difference'];
    } else {
        $left_value = $left['word'];
        $right_value = $right['word'];
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

$minimalpair_from = $minimalpair_from_input;
$minimalpair_to = $minimalpair_to_input;
$minimalpair_from_normalized = normalize_word_for_compare($minimalpair_from);
$minimalpair_to_normalized = normalize_word_for_compare($minimalpair_to);
$has_minimalpair_diff = $minimalpair_enabled && $minimalpair_from !== '' && $minimalpair_to !== '' && $minimalpair_from_normalized !== $minimalpair_to_normalized;

$minimalpair_rules = array();
if ($has_minimalpair_diff) {
    $minimalpair_rules[] = array(
        'from' => $minimalpair_from_normalized,
        'to' => $minimalpair_to_normalized,
        'label' => uppercase_word_for_display($minimalpair_from) . '→' . uppercase_word_for_display($minimalpair_to)
    );

    if ($minimalpair_bidirectional) {
        $minimalpair_rules[] = array(
            'from' => $minimalpair_to_normalized,
            'to' => $minimalpair_from_normalized,
            'label' => uppercase_word_for_display($minimalpair_to) . '→' . uppercase_word_for_display($minimalpair_from)
        );
    }
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

if ($has_minimalpair_diff) {
    $all_filtered_words = get_result("select id, name, image, image_url from words $wh");
    $filtered_words = array();
    $words_by_normalized_name = array();

    while ($row = mysqli_fetch_array($all_filtered_words)) {
        $normalized_name = normalize_word_for_compare($row['name']);
        $entry = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'normalized_name' => $normalized_name,
            'image' => $row['image'],
            'image_url' => $row['image_url']
        );

        $filtered_words[] = $entry;
        if (!isset($words_by_normalized_name[$normalized_name])) {
            $words_by_normalized_name[$normalized_name] = array();
        }
        $words_by_normalized_name[$normalized_name][] = $entry;
    }

    $seen_pairs = array();
    foreach ($filtered_words as $source_word) {
        foreach ($minimalpair_rules as $rule) {
            $from_token = $rule['from'];
            $to_token = $rule['to'];
            $positions = find_all_occurrences($source_word['normalized_name'], $from_token);

            foreach ($positions as $position) {
                $target_name = substr_replace($source_word['normalized_name'], $to_token, $position, strlen($from_token));

                if (!isset($words_by_normalized_name[$target_name])) {
                    continue;
                }

                foreach ($words_by_normalized_name[$target_name] as $target_word) {
                    if ($target_word['id'] == $source_word['id']) {
                        continue;
                    }

                    $pair_key = min($source_word['id'], $target_word['id']) . '_' . max($source_word['id'], $target_word['id']);
                    if (isset($seen_pairs[$pair_key])) {
                        continue;
                    }

                    $seen_pairs[$pair_key] = true;
                    $minimalpair_rows[] = array(
                        'id' => $target_word['id'],
                        'word' => $target_word['name'],
                        'pair_word' => $source_word['name'],
                        'difference' => $rule['label'],
                        'image' => $target_word['image'],
                        'image_url' => $target_word['image_url']
                    );
                }
            }
        }
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
if ($has_minimalpair_diff) {
    foreach ($data as $row) {
        $column_index = 0;

        if ($include_selection) {
            $data2[$i][$column_index++] = '<input type="checkbox" class="memory-select" value="' . $row['id'] . '">';
        }

        $data2[$i][$column_index++] = $row['word'];
        if ($include_selection) {
            $data2[$i][$column_index++] = $row['pair_word'];
            $data2[$i][$column_index++] = $row['difference'];
        }

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
    if ($include_selection) {
        $data2[$i][$column_index++] = '';
        $data2[$i][$column_index++] = '';
    }

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
