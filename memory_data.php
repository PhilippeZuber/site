<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(array("ok" => false, "code" => "UNAUTHORIZED", "message" => "Nicht eingeloggt."));
    exit;
}

require_once('system/data.php');
require_once('system/security.php');

$ids_input = '';
if (isset($_POST['ids'])) {
    if (is_array($_POST['ids'])) {
        $ids_input = implode(',', $_POST['ids']);
    } else {
        $ids_input = $_POST['ids'];
    }
}

$ids_input = filter_data($ids_input);
$ids_input = preg_replace('/[^0-9,]/', '', $ids_input);
$ids = array_filter(array_unique(array_map('intval', explode(',', $ids_input))));

if (count($ids) < 2) {
    http_response_code(400);
    echo json_encode(array("ok" => false, "code" => "INVALID_IDS", "message" => "Bitte mindestens 2 Wörter auswählen."));
    exit;
}

$mode = isset($_POST['mode']) ? filter_data($_POST['mode']) : 'image';
$mode = in_array($mode, array('image', 'text', 'mixed')) ? $mode : 'image';

$pairs = isset($_POST['pairs']) ? filter_data($_POST['pairs']) : '';
$pairs = preg_replace('/[^0-9]/', '', $pairs);
$pairs = $pairs == '' ? 0 : intval($pairs);
if ($pairs > 0 && $pairs > count($ids)) {
    http_response_code(400);
    echo json_encode(array("ok" => false, "code" => "PAIRS_EXCEED_IDS", "message" => "Mehr Paare als ausgewählte Wörter."));
    exit;
}

$id_list = implode(',', $ids);
$sql = "select id, name, image, image_url, lauttreu from words where id in ($id_list)";
$data = get_result($sql);

$words = array();
while ($row = mysqli_fetch_assoc($data)) {
    $words[] = array(
        "id" => intval($row['id']),
        "name" => $row['name'],
        "image" => $row['image'],
        "image_url" => $row['image_url'],
        "lauttreu" => intval($row['lauttreu'])
    );
}

echo json_encode(array(
    "ok" => true,
    "mode" => $mode,
    "pairs" => $pairs,
    "words" => $words
));
