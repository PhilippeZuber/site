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

// MVP placeholder: Bestandskunden erhalten Zugang. Spaeter durch echtes Abo-Flag ersetzen.
$entitled = true;
$plan_code = 'trial';
$billing_period = 'yearly';

api_v1_send_json(200, array(
    'data' => array(
        'user_id' => $user_id,
        'entitled' => $entitled,
        'plan_code' => $plan_code,
        'billing_period' => $billing_period
    )
));
