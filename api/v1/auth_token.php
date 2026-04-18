<?php

session_start();

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/addin_auth.php');

api_v1_handle_options();
api_v1_require_method('POST');

if (!isset($_SESSION['id'])) {
    api_v1_send_json(401, array('error' => 'not_authenticated'));
}

$ttl_seconds = 60 * 60 * 8;
$token = issue_addin_token($_SESSION['id'], $ttl_seconds);

api_v1_send_json(200, array(
    'token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => $ttl_seconds,
    'user_id' => intval($_SESSION['id'])
));
