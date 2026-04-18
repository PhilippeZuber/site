<?php

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/data.php');
require_once(__DIR__ . '/../../system/addin_auth.php');

api_v1_handle_options();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_v1_send_json(405, array('error' => 'method_not_allowed'));
}

$user_id = get_addin_user_id_from_auth_header();
if ($user_id === false) {
    api_v1_send_json(401, array('error' => 'invalid_or_missing_token'));
}

$input = api_v1_get_input();

$search_text = isset($input['search_text']) ? trim((string)$input['search_text']) : '';
$not_letter = isset($input['not_letter']) ? trim((string)$input['not_letter']) : '';
$category = isset($input['category']) ? api_v1_int_array($input['category']) : array();
$semantic = isset($input['semantic']) ? api_v1_int_array($input['semantic']) : array();
$alter = isset($input['alter']) ? api_v1_int_array($input['alter']) : array();
$lauttreu = isset($input['lauttreu']) ? api_v1_to_bool($input['lauttreu']) : false;
$image_mode = (isset($input['image_mode']) && $input['image_mode'] === 'ausmalbild') ? 'ausmalbild' : 'standard';
$page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
$page_size = isset($input['page_size']) ? intval($input['page_size']) : 25;
$page_size = max(1, min(100, $page_size));
$offset = ($page - 1) * $page_size;

$db = get_db_connection();

$where_parts = array('1=1');
$params = array();
$types = '';

if ($search_text !== '') {
    $clean = str_replace('*', '', $search_text);
    if ($clean !== '') {
        if (substr($search_text, -1) === '*' && substr($search_text, 0, 1) === '*') {
            $where_parts[] = 'name LIKE ?';
            $params[] = '%' . $clean . '%';
            $types .= 's';
        } elseif (substr($search_text, -1) === '*') {
            $where_parts[] = 'name LIKE ?';
            $params[] = $clean . '%';
            $types .= 's';
        } elseif (substr($search_text, 0, 1) === '*') {
            $where_parts[] = 'name LIKE ?';
            $params[] = '%' . $clean;
            $types .= 's';
        } else {
            $where_parts[] = 'name LIKE ?';
            $params[] = '%' . $clean . '%';
            $types .= 's';
        }
    }
}

if ($not_letter !== '') {
    $where_parts[] = 'name NOT LIKE ?';
    $params[] = '%' . $not_letter . '%';
    $types .= 's';
}

if (!empty($category)) {
    $placeholders = implode(',', array_fill(0, count($category), '?'));
    $where_parts[] = 'category IN (' . $placeholders . ')';
    foreach ($category as $item) {
        $params[] = $item;
        $types .= 'i';
    }
}

if (!empty($semantic)) {
    $semantic_parts = array();
    foreach ($semantic as $item) {
        $semantic_parts[] = '(semantic LIKE ? OR semantic LIKE ? OR semantic LIKE ? OR semantic = ?)';
        $params[] = '%,' . $item . ',%';
        $params[] = '%,' . $item;
        $params[] = $item . ',%';
        $params[] = (string)$item;
        $types .= 'ssss';
    }
    $where_parts[] = '(' . implode(' OR ', $semantic_parts) . ')';
}

if (!empty($alter)) {
    $placeholders = implode(',', array_fill(0, count($alter), '?'));
    $where_parts[] = 'alters IN (' . $placeholders . ')';
    foreach ($alter as $item) {
        $params[] = $item;
        $types .= 'i';
    }
}

if ($lauttreu) {
    $where_parts[] = 'lauttreu = 1';
}

$where_sql = ' WHERE ' . implode(' AND ', $where_parts);

$count_sql = 'SELECT COUNT(*) AS count FROM words' . $where_sql;
$count_stmt = mysqli_prepare($db, $count_sql);
if ($count_stmt === false) {
    mysqli_close($db);
    api_v1_send_json(500, array('error' => 'count_prepare_failed'));
}

if ($types !== '') {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}

mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$count_row = mysqli_fetch_assoc($count_result);
$total_filtered = intval($count_row['count']);
mysqli_stmt_close($count_stmt);

$total_sql = 'SELECT COUNT(*) AS count FROM words';
$total_result = mysqli_query($db, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_count = intval($total_row['count']);

$data_sql = 'SELECT id, name, image, image_ausmalbild, image_url, category, semantic, alters, lauttreu FROM words'
    . $where_sql . ' ORDER BY name ASC LIMIT ? OFFSET ?';
$data_stmt = mysqli_prepare($db, $data_sql);
if ($data_stmt === false) {
    mysqli_close($db);
    api_v1_send_json(500, array('error' => 'data_prepare_failed'));
}

$data_params = $params;
$data_types = $types . 'ii';
$data_params[] = $page_size;
$data_params[] = $offset;

mysqli_stmt_bind_param($data_stmt, $data_types, ...$data_params);
mysqli_stmt_execute($data_stmt);
$data_result = mysqli_stmt_get_result($data_stmt);

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$base_url = $host !== '' ? $scheme . '://' . $host : '';

$rows = array();
while ($row = mysqli_fetch_assoc($data_result)) {
    $image_file = ($image_mode === 'ausmalbild' && !empty($row['image_ausmalbild'])) ? $row['image_ausmalbild'] : $row['image'];
    $local_url = '';
    if (!empty($image_file)) {
        $local_url = ($base_url !== '' ? $base_url : '') . '/images/' . $image_file;
    }

    $semantic_ids = array();
    if (!empty($row['semantic'])) {
        foreach (explode(',', $row['semantic']) as $id) {
            $id = intval(trim($id));
            if ($id > 0) {
                $semantic_ids[] = $id;
            }
        }
        $semantic_ids = array_values(array_unique($semantic_ids));
    }

    $rows[] = array(
        'id' => intval($row['id']),
        'name' => $row['name'],
        'category_id' => intval($row['category']),
        'semantic_ids' => $semantic_ids,
        'alter_id' => intval($row['alters']),
        'lauttreu' => intval($row['lauttreu']) === 1,
        'image_local_url' => $local_url,
        'image_external_url' => $row['image_url'],
        'image_mode' => $image_mode
    );
}

mysqli_stmt_close($data_stmt);
mysqli_close($db);

api_v1_send_json(200, array(
    'meta' => array(
        'user_id' => $user_id,
        'page' => $page,
        'page_size' => $page_size,
        'total' => $total_count,
        'total_filtered' => $total_filtered
    ),
    'data' => $rows
));
