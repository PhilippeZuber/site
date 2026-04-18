<?php

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/data.php');
require_once(__DIR__ . '/../../system/security.php');
require_once(__DIR__ . '/../../system/addin_auth.php');

function api_v1_sanitize_word_ids($word_ids_raw) {
    $word_ids_raw = trim((string)$word_ids_raw);
    if ($word_ids_raw === '') {
        return '';
    }

    $word_ids_raw = preg_replace('/[^0-9,]/', '', $word_ids_raw);
    $parts = array_filter(explode(',', $word_ids_raw), function ($value) {
        return $value !== '';
    });

    $unique = array();
    foreach ($parts as $part) {
        $int_part = intval($part);
        if ($int_part > 0) {
            $unique[$int_part] = true;
        }
    }

    return implode(',', array_keys($unique));
}

function api_v1_collection_to_response($collection) {
    $word_ids = array();
    if (!empty($collection['word_ids'])) {
        foreach (explode(',', $collection['word_ids']) as $id) {
            $id = intval(trim($id));
            if ($id > 0) {
                $word_ids[] = $id;
            }
        }
        $word_ids = array_values(array_unique($word_ids));
    }

    return array(
        'id' => intval($collection['id']),
        'user_id' => intval($collection['user_id']),
        'name' => $collection['name'],
        'word_ids' => $word_ids
    );
}

api_v1_handle_options();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_v1_send_json(405, array('error' => 'method_not_allowed'));
}

$user_id = get_addin_user_id_from_auth_header();
if ($user_id === false) {
    api_v1_send_json(401, array('error' => 'invalid_or_missing_token'));
}

$input = api_v1_get_input();
$action = isset($input['action']) ? trim((string)$input['action']) : '';

if ($action === 'list') {
    $collections = get_word_collections($user_id);
    $result = array();
    foreach ($collections as $collection) {
        $result[] = api_v1_collection_to_response($collection);
    }

    api_v1_send_json(200, array('collections' => $result));
}

if ($action === 'get') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) {
        api_v1_send_json(400, array('error' => 'invalid_request', 'details' => 'id_required'));
    }

    $collection = get_word_collection($user_id, $id);
    if (empty($collection)) {
        api_v1_send_json(404, array('error' => 'not_found'));
    }

    api_v1_send_json(200, array('collection' => api_v1_collection_to_response($collection)));
}

if ($action === 'create') {
    $name = isset($input['name']) ? trim(filter_data($input['name'])) : '';
    $word_ids = isset($input['word_ids']) ? api_v1_sanitize_word_ids($input['word_ids']) : '';

    if ($name === '') {
        api_v1_send_json(400, array('error' => 'name_required'));
    }

    $new_id = add_word_collection($user_id, $name, $word_ids);
    $collection = get_word_collection($user_id, $new_id);

    api_v1_send_json(201, array('collection' => api_v1_collection_to_response($collection)));
}

if ($action === 'update') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $name = isset($input['name']) ? trim(filter_data($input['name'])) : '';
    $word_ids = isset($input['word_ids']) ? api_v1_sanitize_word_ids($input['word_ids']) : '';

    if ($id <= 0 || $name === '') {
        api_v1_send_json(400, array('error' => 'invalid_request'));
    }

    $existing = get_word_collection($user_id, $id);
    if (empty($existing)) {
        api_v1_send_json(404, array('error' => 'not_found'));
    }

    update_word_collection($user_id, $id, $name, $word_ids);
    $collection = get_word_collection($user_id, $id);

    api_v1_send_json(200, array('collection' => api_v1_collection_to_response($collection)));
}

if ($action === 'delete') {
    $id = isset($input['id']) ? intval($input['id']) : 0;
    if ($id <= 0) {
        api_v1_send_json(400, array('error' => 'invalid_request'));
    }

    $existing = get_word_collection($user_id, $id);
    if (empty($existing)) {
        api_v1_send_json(404, array('error' => 'not_found'));
    }

    delete_word_collection($user_id, $id);
    api_v1_send_json(200, array('status' => 'ok'));
}

api_v1_send_json(400, array('error' => 'unknown_action'));
