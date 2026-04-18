<?php

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/data.php');
require_once(__DIR__ . '/../../system/addin_auth.php');

api_v1_handle_options();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_v1_send_json(405, array('error' => 'method_not_allowed'));
}

$user_id = get_addin_user_id_from_auth_header();
if ($user_id === false) {
    api_v1_send_json(401, array('error' => 'invalid_or_missing_token'));
}

$input = api_v1_get_input();
$id = isset($input['id']) ? intval($input['id']) : 0;
if ($id <= 0) {
    api_v1_send_json(400, array('error' => 'invalid_request', 'details' => 'id_required'));
}

$db = get_db_connection();
$sql = 'SELECT id, name, image, image_ausmalbild, image_url, category, semantic, alters, lauttreu FROM words WHERE id = ? LIMIT 1';
$stmt = mysqli_prepare($db, $sql);
if ($stmt === false) {
    mysqli_close($db);
    api_v1_send_json(500, array('error' => 'prepare_failed'));
}

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (empty($row)) {
    mysqli_close($db);
    api_v1_send_json(404, array('error' => 'not_found'));
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$base_url = $host !== '' ? $scheme . '://' . $host : '';

$local_standard = '';
if (!empty($row['image'])) {
    $local_standard = ($base_url !== '' ? $base_url : '') . '/images/' . $row['image'];
}

$local_ausmalbild = '';
if (!empty($row['image_ausmalbild'])) {
    $local_ausmalbild = ($base_url !== '' ? $base_url : '') . '/images/' . $row['image_ausmalbild'];
}

$semantic_ids = array();
if (!empty($row['semantic'])) {
    foreach (explode(',', $row['semantic']) as $semantic_id) {
        $semantic_id = intval(trim($semantic_id));
        if ($semantic_id > 0) {
            $semantic_ids[] = $semantic_id;
        }
    }
    $semantic_ids = array_values(array_unique($semantic_ids));
}

mysqli_close($db);

api_v1_send_json(200, array(
    'meta' => array(
        'user_id' => $user_id
    ),
    'data' => array(
        'id' => intval($row['id']),
        'name' => $row['name'],
        'category_id' => intval($row['category']),
        'semantic_ids' => $semantic_ids,
        'alter_id' => intval($row['alters']),
        'lauttreu' => intval($row['lauttreu']) === 1,
        'image_local_standard_url' => $local_standard,
        'image_local_ausmalbild_url' => $local_ausmalbild,
        'image_external_url' => $row['image_url']
    )
));
