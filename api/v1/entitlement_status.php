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

$entitlement = get_addin_entitlement($user_id);

api_v1_send_json(200, array(
    'data' => array(
        'user_id' => $user_id,
        'entitled' => $entitlement['entitled'],
        'plan_code' => $entitlement['plan_code'],
        'billing_period' => $entitlement['billing_period'],
        'status' => $entitlement['status'],
        'expires_at' => $entitlement['expires_at']
    )
));
