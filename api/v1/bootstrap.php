<?php

function api_v1_set_cors_headers() {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $allowed_origins_raw = getenv('WORTLAB_ADDIN_ALLOWED_ORIGINS');

    if ($allowed_origins_raw !== false && trim($allowed_origins_raw) !== '') {
        $allowed = array();
        foreach (explode(',', $allowed_origins_raw) as $item) {
            $item = trim($item);
            if ($item !== '') {
                $allowed[] = $item;
            }
        }

        if ($origin !== '' && in_array($origin, $allowed, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }
    } else {
        if ($origin !== '') {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Vary: Origin');
        }
    }

    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}

function api_v1_send_json($status_code, $data) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    api_v1_set_cors_headers();

    echo json_encode($data);
    exit;
}

function api_v1_handle_options() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        api_v1_send_json(200, array('status' => 'ok'));
    }
}

function api_v1_get_input() {
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
    $input = array();

    if (stripos($content_type, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        if ($raw !== false && trim($raw) !== '') {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                $input = $parsed;
            }
        }
    }

    if (empty($input)) {
        if (!empty($_POST)) {
            $input = $_POST;
        } elseif (!empty($_GET)) {
            $input = $_GET;
        }
    }

    return $input;
}

function api_v1_require_method($method) {
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        api_v1_send_json(405, array('error' => 'method_not_allowed'));
    }
}

function api_v1_as_array($value) {
    if (is_array($value)) {
        return $value;
    }

    if ($value === null || $value === '') {
        return array();
    }

    if (is_string($value) && strpos($value, ',') !== false) {
        return explode(',', $value);
    }

    return array($value);
}

function api_v1_int_array($value) {
    $items = api_v1_as_array($value);
    $result = array();

    foreach ($items as $item) {
        if ($item === '' || $item === null) {
            continue;
        }
        $int_item = intval($item);
        if ($int_item > 0) {
            $result[] = $int_item;
        }
    }

    return array_values(array_unique($result));
}

function api_v1_to_bool($value) {
    if (is_bool($value)) {
        return $value;
    }

    if (is_string($value)) {
        $value = strtolower(trim($value));
        return in_array($value, array('1', 'true', 'yes', 'on'), true);
    }

    return intval($value) === 1;
}
