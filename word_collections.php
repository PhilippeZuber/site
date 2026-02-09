<?php
session_start();
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(array('error' => 'not_authenticated'));
    exit;
}

require_once('system/data.php');
require_once('system/security.php');

header('Content-Type: application/json; charset=utf-8');

$user_id = filter_data($_SESSION['id']);
$action = isset($_POST['action']) ? filter_data($_POST['action']) : '';

function sanitize_word_ids($word_ids_raw) {
    $word_ids_raw = trim($word_ids_raw);
    if ($word_ids_raw === '') {
        return '';
    }
    $word_ids_raw = preg_replace('/[^0-9,]/', '', $word_ids_raw);
    $parts = array_filter(explode(',', $word_ids_raw), function ($value) {
        return $value !== '';
    });
    $unique = array();
    foreach ($parts as $part) {
        $unique[$part] = true;
    }
    return implode(',', array_keys($unique));
}

if ($action === 'list') {
    $collections = get_word_collections($user_id);
    echo json_encode(array('collections' => $collections));
    exit;
}

if ($action === 'get') {
    $id = isset($_POST['id']) ? filter_data($_POST['id']) : '';
    $collection = get_word_collection($user_id, $id);
    if (empty($collection)) {
        http_response_code(404);
        echo json_encode(array('error' => 'not_found'));
        exit;
    }
    echo json_encode(array('collection' => $collection));
    exit;
}

if ($action === 'create') {
    $name = isset($_POST['name']) ? filter_data($_POST['name']) : '';
    $word_ids = isset($_POST['word_ids']) ? sanitize_word_ids($_POST['word_ids']) : '';
    if ($name === '') {
        http_response_code(400);
        echo json_encode(array('error' => 'name_required'));
        exit;
    }
    $new_id = add_word_collection($user_id, $name, $word_ids);
    echo json_encode(array('id' => $new_id));
    exit;
}

if ($action === 'update') {
    $id = isset($_POST['id']) ? filter_data($_POST['id']) : '';
    $name = isset($_POST['name']) ? filter_data($_POST['name']) : '';
    $word_ids = isset($_POST['word_ids']) ? sanitize_word_ids($_POST['word_ids']) : '';
    if ($id === '' || $name === '') {
        http_response_code(400);
        echo json_encode(array('error' => 'invalid_request'));
        exit;
    }
    update_word_collection($user_id, $id, $name, $word_ids);
    echo json_encode(array('status' => 'ok'));
    exit;
}

if ($action === 'delete') {
    $id = isset($_POST['id']) ? filter_data($_POST['id']) : '';
    if ($id === '') {
        http_response_code(400);
        echo json_encode(array('error' => 'invalid_request'));
        exit;
    }
    delete_word_collection($user_id, $id);
    echo json_encode(array('status' => 'ok'));
    exit;
}

http_response_code(400);
echo json_encode(array('error' => 'unknown_action'));
