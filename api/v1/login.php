<?php

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/addin_auth.php');
require_once(__DIR__ . '/../../system/data.php');
require_once(__DIR__ . '/../../system/security.php');

api_v1_handle_options();
api_v1_require_method('POST');

$input = api_v1_get_input();

$identifier_raw = '';
if (isset($input['identifier'])) {
    $identifier_raw = $input['identifier'];
} elseif (isset($input['email'])) {
    $identifier_raw = $input['email'];
}

$password_raw = isset($input['password']) ? $input['password'] : '';

if (!is_string($identifier_raw) || !is_string($password_raw)) {
    api_v1_send_json(400, array('error' => 'invalid_payload'));
}

$identifier_raw = trim($identifier_raw);
$password_raw = trim($password_raw);

if ($identifier_raw === '' || $password_raw === '') {
    api_v1_send_json(400, array('error' => 'missing_credentials'));
}

$identifier = filter_data($identifier_raw);
$password_hash = md5(filter_data($password_raw));

$result = login($identifier, $password_hash);
if (!$result || mysqli_num_rows($result) !== 1) {
    api_v1_send_json(401, array('error' => 'invalid_credentials'));
}

$user = mysqli_fetch_assoc($result);
if (!$user || !isset($user['user_id'])) {
    api_v1_send_json(401, array('error' => 'invalid_credentials'));
}

$user_id = intval($user['user_id']);
$role = isset($user['role']) ? intval($user['role']) : 2;

$ttl_seconds = 60 * 60 * 8;
$token = issue_addin_token($user_id, $ttl_seconds);

log_login_success($user_id, $role, 'addin_login');

api_v1_send_json(200, array(
    'token' => $token,
    'token_type' => 'Bearer',
    'expires_in' => $ttl_seconds,
    'user_id' => $user_id,
    'role' => $role
));
