<?php

require_once(__DIR__ . '/bootstrap.php');
require_once(__DIR__ . '/../../system/data.php');
require_once(__DIR__ . '/../../system/addin_auth.php');

api_v1_handle_options();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    api_v1_send_json(405, array('error' => 'method_not_allowed'));
}

$user_id = get_addin_user_id_from_auth_header();
if ($user_id === false) {
    api_v1_send_json(401, array('error' => 'invalid_or_missing_token'));
}

$input = api_v1_get_input();
$id = isset($input['id']) ? intval($input['id']) : 0;
$image_mode = (isset($input['image_mode']) && $input['image_mode'] === 'ausmalbild') ? 'ausmalbild' : 'standard';

if ($id <= 0) {
    api_v1_send_json(400, array('error' => 'invalid_request', 'details' => 'id_required'));
}

function api_v1_fetch_remote_binary($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Wortlab-Word-Addin/1.0');
        $body = curl_exec($ch);
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $content_type = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($body === false || $status >= 400 || $status === 0) {
            return false;
        }

        return array('body' => $body, 'content_type' => $content_type);
    }

    $context = stream_context_create(array(
        'http' => array(
            'timeout' => 20,
            'follow_location' => 1,
            'user_agent' => 'Wortlab-Word-Addin/1.0'
        ),
        'ssl' => array(
            'verify_peer' => true,
            'verify_peer_name' => true
        )
    ));

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return false;
    }

    $content_type = '';
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $header_line) {
            if (stripos($header_line, 'Content-Type:') === 0) {
                $content_type = trim(substr($header_line, strlen('Content-Type:')));
                break;
            }
        }
    }

    return array('body' => $body, 'content_type' => $content_type);
}

$db = get_db_connection();
$sql = 'SELECT image, image_ausmalbild, image_url FROM words WHERE id = ? LIMIT 1';
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
mysqli_close($db);

if (empty($row)) {
    api_v1_send_json(404, array('error' => 'not_found'));
}

$local_image = ($image_mode === 'ausmalbild' && !empty($row['image_ausmalbild'])) ? $row['image_ausmalbild'] : $row['image'];
$binary = false;
$content_type = '';

if (!empty($local_image)) {
    $local_path = realpath(__DIR__ . '/../../images/' . $local_image);
    $images_root = realpath(__DIR__ . '/../../images');

    if ($local_path !== false && $images_root !== false && strpos($local_path, $images_root) === 0 && is_file($local_path)) {
        $binary = @file_get_contents($local_path);
        if ($binary !== false) {
            if (function_exists('mime_content_type')) {
                $content_type = (string)mime_content_type($local_path);
            }
            if ($content_type === '') {
                $extension = strtolower(pathinfo($local_path, PATHINFO_EXTENSION));
                if ($extension === 'png') {
                    $content_type = 'image/png';
                } elseif ($extension === 'gif') {
                    $content_type = 'image/gif';
                } elseif ($extension === 'webp') {
                    $content_type = 'image/webp';
                } elseif ($extension === 'svg') {
                    $content_type = 'image/svg+xml';
                } else {
                    $content_type = 'image/jpeg';
                }
            }
        }
    }
}

if ($binary === false && !empty($row['image_url'])) {
    $remote = api_v1_fetch_remote_binary($row['image_url']);
    if ($remote !== false) {
        $binary = $remote['body'];
        $content_type = $remote['content_type'];
    }
}

if ($binary === false || $binary === '') {
    api_v1_send_json(404, array('error' => 'image_not_found'));
}

if ($content_type === '') {
    $content_type = 'application/octet-stream';
}

http_response_code(200);
header('Content-Type: ' . $content_type);
header('Content-Length: ' . strlen($binary));
header('Cache-Control: private, max-age=300');
api_v1_set_cors_headers();
header('X-Request-Id: ' . api_v1_get_request_id());
echo $binary;
exit;