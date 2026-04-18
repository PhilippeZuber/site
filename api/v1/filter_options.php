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

$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');

$map_records = function ($rows) {
    $result = array();
    foreach ($rows as $row) {
        $result[] = array(
            'id' => intval($row['id']),
            'name' => $row['name']
        );
    }
    return $result;
};

api_v1_send_json(200, array(
    'meta' => array(
        'user_id' => $user_id
    ),
    'data' => array(
        'category' => $map_records($categories),
        'semantic' => $map_records($semantic),
        'alter' => $map_records($alters)
    )
));
