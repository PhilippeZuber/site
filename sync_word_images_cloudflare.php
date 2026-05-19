<?php

require_once(__DIR__ . '/system/data.php');

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Dieses Skript darf nur per CLI ausgeführt werden.\n");
    exit(1);
}

if (!extension_loaded('mysqli')) {
    fwrite(STDERR, "Die PHP-CLI-Erweiterung mysqli ist nicht geladen. Verwende eine PHP-CLI mit MySQL-Unterstützung, damit das Skript auf words zugreifen kann.\n");
    exit(1);
}

const DEFAULT_MODEL = '@cf/black-forest-labs/flux-1-schnell';
const DEFAULT_STEPS = 4;
const DEFAULT_TIMEOUT = 180;

$options = parse_cli_options($argv);

if ($options['help']) {
    print_help();
    exit(0);
}

$images_dir = __DIR__ . '/images';
if (!is_dir($images_dir) && !mkdir($images_dir, 0775, true) && !is_dir($images_dir)) {
    fwrite(STDERR, "images-Verzeichnis konnte nicht erstellt werden: $images_dir\n");
    exit(1);
}

$words = get_word_candidates($options['word_id'], $options['limit']);
if (empty($words)) {
    fwrite(STDOUT, "Keine passenden Wörter gefunden.\n");
    exit(0);
}

$cloudflare = array(
    'account_id' => trim((string) getenv('CLOUDFLARE_ACCOUNT_ID')),
    'api_token' => trim((string) getenv('CLOUDFLARE_API_TOKEN')),
    'model' => trim((string) getenv('CLOUDFLARE_AI_MODEL')),
    'style_prompt' => trim((string) getenv('WORDLAB_IMAGE_STYLE_PROMPT')),
);

if ($cloudflare['model'] === '') {
    $cloudflare['model'] = DEFAULT_MODEL;
}

$needs_generation = false;
foreach ($words as $word) {
    if (!word_has_local_image($word, $images_dir)) {
        $needs_generation = true;
        break;
    }
}

if ($needs_generation) {
    if ($cloudflare['account_id'] === '' || $cloudflare['api_token'] === '') {
        fwrite(STDERR, "CLOUDFLARE_ACCOUNT_ID und CLOUDFLARE_API_TOKEN sind erforderlich, sobald Bilder generiert werden müssen.\n");
        exit(1);
    }
}

$stats = array(
    'checked' => 0,
    'skipped' => 0,
    'generated' => 0,
    'failed' => 0,
    'updated' => 0,
);

foreach ($words as $word) {
    $stats['checked']++;
    $word_id = (int) $word['id'];
    $word_name = $word['name'];

    if (word_has_local_image($word, $images_dir)) {
        log_line($word_id, $word_name, 'skip', 'Lokales Bild bereits vorhanden.');
        $stats['skipped']++;
        continue;
    }

    try {
        $filename = generate_word_image($word, $images_dir, $cloudflare, $options['dry_run']);

        if (!$options['dry_run']) {
            update_local_image_filename($word_id, $filename);
            $stats['updated']++;
        }

        $stats['generated']++;
        log_line($word_id, $word_name, 'generate', $filename);
    } catch (RuntimeException $exception) {
        $stats['failed']++;
        log_line($word_id, $word_name, 'error', $exception->getMessage());
    }
}

fwrite(STDOUT, "\nZusammenfassung\n");
fwrite(STDOUT, "Geprüft: {$stats['checked']}\n");
fwrite(STDOUT, "Übersprungen: {$stats['skipped']}\n");
fwrite(STDOUT, "Generiert: {$stats['generated']}\n");
fwrite(STDOUT, "DB-Updates: {$stats['updated']}\n");
fwrite(STDOUT, "Fehler: {$stats['failed']}\n");

exit($stats['failed'] > 0 ? 2 : 0);

function parse_cli_options($argv) {
    $options = array(
        'limit' => 25,
        'word_id' => 0,
        'dry_run' => false,
        'help' => false,
    );

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--dry-run') {
            $options['dry_run'] = true;
            continue;
        }

        if ($argument === '--help' || $argument === '-h') {
            $options['help'] = true;
            continue;
        }

        if (strpos($argument, '--limit=') === 0) {
            $options['limit'] = max(1, (int) substr($argument, 8));
            continue;
        }

        if (strpos($argument, '--word-id=') === 0) {
            $options['word_id'] = max(0, (int) substr($argument, 10));
            continue;
        }
    }

    return $options;
}

function print_help() {
    $help = <<<TXT
Verwendung:
    php sync_word_images_cloudflare.php [--limit=25] [--word-id=123] [--dry-run]

Umgebungsvariablen:
  CLOUDFLARE_ACCOUNT_ID       erforderlich fuer Generierung
  CLOUDFLARE_API_TOKEN        erforderlich fuer Generierung
  CLOUDFLARE_AI_MODEL         optional, Standard: @cf/black-forest-labs/flux-1-schnell
  WORDLAB_IMAGE_STYLE_PROMPT  optional, ueberschreibt den Standard-Stilzusatz

Verhalten:
  - Behaelt vorhandene lokale Bilder bei
    - Laesst image_url unangetastet, damit externe Bilder extern bleiben
    - Generiert fuer fehlende lokale Bild-Faelle ein neues Bild via Cloudflare
  - Schreibt den lokalen Dateinamen nach words.image
TXT;

    fwrite(STDOUT, $help . "\n");
}

function get_word_candidates($word_id, $limit) {
    $db = get_db_connection();

    $sql = "SELECT w.id, w.name, w.image, w.image_url, w.category, w.semantic, w.alters, c.name AS category_name, a.name AS alter_name, GROUP_CONCAT(DISTINCT s.name ORDER BY s.id SEPARATOR ', ') AS semantic_names FROM words w LEFT JOIN category c ON c.id = w.category LEFT JOIN alters a ON a.id = w.alters LEFT JOIN semantic s ON FIND_IN_SET(s.id, w.semantic) > 0";
    $where = array();
    if ($word_id > 0) {
        $where[] = "w.id = '" . mysqli_real_escape_string($db, (string) $word_id) . "'";
    } else {
        $where[] = "(w.image IS NULL OR w.image = '')";
    }
    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' GROUP BY w.id ORDER BY w.name ASC';
    if ($word_id === 0 && $limit > 0) {
        $sql .= ' LIMIT ' . (int) $limit;
    }

    $result = mysqli_query($db, $sql);
    if ($result === false) {
        $error = mysqli_error($db);
        mysqli_close($db);
        throw new RuntimeException('SQL-Fehler beim Laden der Wörter: ' . $error);
    }

    $rows = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_close($db);
    return $rows;
}

function word_has_local_image($word, $images_dir) {
    $filename = trim((string) $word['image']);
    if ($filename === '') {
        return false;
    }

    $full_path = $images_dir . DIRECTORY_SEPARATOR . $filename;
    return is_file($full_path) && filesize($full_path) > 0;
}

function generate_word_image($word, $images_dir, $cloudflare, $dry_run) {
    $payload = array(
        'prompt' => build_word_prompt($word, $cloudflare['style_prompt']),
        'steps' => DEFAULT_STEPS,
        'seed' => random_int(1, 2147483647),
    );

    $response = cloudflare_generate_image($payload, $cloudflare['account_id'], $cloudflare['api_token'], $cloudflare['model']);
    if (empty($response['image'])) {
        throw new RuntimeException('Cloudflare lieferte kein Bild zurück.');
    }

    $binary = base64_decode($response['image'], true);
    if ($binary === false || $binary === '') {
        throw new RuntimeException('Cloudflare-Bild konnte nicht decodiert werden.');
    }

    $filename = pick_local_filename($word, 'jpg');
    if (!$dry_run) {
        $full_path = $images_dir . DIRECTORY_SEPARATOR . $filename;
        write_binary_file($full_path, $binary);
    }

    return $filename;
}

function build_word_prompt($word, $style_prompt_override) {
    $parts = array();
    $parts[] = 'Erstelle eine klare, didaktische Illustration fuer eine Wortbildkarte fuer Kinder.';
    $parts[] = 'Das Zielwort ist "' . $word['name'] . '".';

    if (!empty($word['category_name'])) {
        $parts[] = 'Wortart: ' . $word['category_name'] . '.';
    }
    if (!empty($word['semantic_names'])) {
        $parts[] = 'Themenbezug: ' . $word['semantic_names'] . '.';
    }
    if (!empty($word['alter_name'])) {
        $parts[] = 'Geeignet fuer: ' . $word['alter_name'] . '.';
    }

    if ($style_prompt_override !== '') {
        $parts[] = $style_prompt_override;
    } else {
        $parts[] = 'Style: children\'s educational word-picture card, cartoon comic illustration, thick dark outlines, flat colors with slight texture, vivid and friendly palette, pure white background.';
        $parts[] = 'Show exactly one clearly recognizable central motif on a pure white background.';
        $parts[] = 'Avoid culturally specific elements from other countries than Switzerland a Swiss child would not know.';
        $parts[] = 'No text, no letters, no numbers, no frame, no watermark, no collage, no shadow, no gradient background.';
        $parts[] = 'Minimal or no secondary elements so the target motif remains unambiguous.';
    }

    return implode(' ', $parts);
}

function cloudflare_generate_image($payload, $account_id, $api_token, $model) {
    $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account_id) . '/ai/run/' . rawurlencode($model);
    $body = json_encode($payload);
    if ($body === false) {
        throw new RuntimeException('Request-Body konnte nicht serialisiert werden.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('cURL wird fuer Cloudflare Workers AI benoetigt.');
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_TIMEOUT, DEFAULT_TIMEOUT);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $api_token,
        'Content-Type: application/json',
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $response = curl_exec($ch);
    $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        throw new RuntimeException('Cloudflare-Request fehlgeschlagen: ' . $curl_error);
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Ungültige Cloudflare-Antwort: HTTP ' . $http_code);
    }

    if ($http_code >= 400 || (isset($decoded['success']) && $decoded['success'] === false)) {
        $message = 'Cloudflare-Fehler';
        if (!empty($decoded['errors'][0]['message'])) {
            $message .= ': ' . $decoded['errors'][0]['message'];
        } elseif (!empty($decoded['result']['description'])) {
            $message .= ': ' . $decoded['result']['description'];
        }
        throw new RuntimeException($message);
    }

    if (isset($decoded['result']) && is_array($decoded['result'])) {
        return $decoded['result'];
    }

    return $decoded;
}


function pick_local_filename($word, $extension) {
    $existing = trim((string) $word['image']);
    if ($existing !== '') {
        return basename($existing);
    }

    $slug = slugify($word['name']);
    return 'word_' . (int) $word['id'] . '_' . $slug . '_' . generateRandomString(8) . '.' . $extension;
}

function slugify($value) {
    $value = strtolower((string) $value);
    $value = str_replace(array('ä', 'ö', 'ü', 'à', 'á', 'â', 'ã', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ø', 'œ', 'ù', 'ú', 'û', 'ý', 'ÿ'), array('ae', 'oe', 'ue', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'oe', 'u', 'u', 'u', 'y', 'y'), $value);
    $value = preg_replace('/[^a-z0-9]+/', '_', $value);
    $value = trim((string) $value, '_');
    return $value !== '' ? $value : 'wort';
}

function write_binary_file($full_path, $binary) {
    $written = @file_put_contents($full_path, $binary);
    if ($written === false || $written === 0) {
        throw new RuntimeException('Datei konnte nicht geschrieben werden: ' . $full_path);
    }
}

function update_local_image_filename($word_id, $filename) {
    $db = get_db_connection();
    $word_id = (int) $word_id;
    $filename = mysqli_real_escape_string($db, $filename);
    $sql = "UPDATE words SET image = '" . $filename . "' WHERE id = '" . $word_id . "'";
    $result = mysqli_query($db, $sql);
    if ($result === false) {
        $error = mysqli_error($db);
        mysqli_close($db);
        throw new RuntimeException('DB-Update fehlgeschlagen: ' . $error);
    }

    mysqli_close($db);
}

function log_line($word_id, $word_name, $action, $message) {
    fwrite(STDOUT, '[' . $action . '] #' . $word_id . ' ' . $word_name . ' -> ' . $message . "\n");
}