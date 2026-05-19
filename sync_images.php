<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once(__DIR__ . '/system/data.php');
require_once(__DIR__ . '/system/security.php');

$page = 'sync_images';
$action = isset($_GET['action']) ? filter_data($_GET['action']) : 'view';

// AJAX-Endpoint für Bildgenerierung
if ($action === 'generate_ajax') {
    header('Content-Type: application/json');
    
    $cloudflare_account_id = getenv('CLOUDFLARE_ACCOUNT_ID');
    $cloudflare_api_token = getenv('CLOUDFLARE_API_TOKEN');
    $cloudflare_model = getenv('CLOUDFLARE_AI_MODEL') ?: '';
    $style_prompt_override = getenv('WORDLAB_IMAGE_STYLE_PROMPT') ?: '';
    
    if (!$cloudflare_account_id || !$cloudflare_api_token) {
        echo json_encode(['error' => 'Cloudflare-Credentials nicht gesetzt (CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_API_TOKEN)']);
        exit;
    }
    
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
    $word_id = isset($_GET['word_id']) ? (int)$_GET['word_id'] : null;
    
    $result = sync_images_batch($cloudflare_account_id, $cloudflare_api_token, $cloudflare_model, $style_prompt_override, $limit, $word_id);
    echo json_encode($result);
    exit;
}

include 'header.php';
?>

<div class="wrapper">
    <?php include 'sidebar.php'; ?>
    <!-- Page Content Holder -->
    <div id="content">
        <?php include 'navigation.php'; ?>
        <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1>Wortbilder generieren (Cloudflare AI)</h1>
                <p class="lead">Generiere fehlende lokale Bilder für Wörter mittels Cloudflare Workers AI FLUX.1</p>
                
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Einstellungen</h3>
                    </div>
                    <div class="panel-body">
                        <form id="syncForm" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Limit (Anzahl Wörter):</label>
                                <div class="col-sm-3">
                                    <input type="number" id="limit" name="limit" class="form-control" value="10" min="1" max="500">
                                    <small class="form-text text-muted">Wörter pro Lauf (Standard: 10)</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Spezifische Wort-ID (optional):</label>
                                <div class="col-sm-3">
                                    <input type="number" id="word_id" name="word_id" class="form-control" placeholder="z.B. 123" min="1">
                                    <small class="form-text text-muted">Lasse leer um alle fehlenden zu generieren</small>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-9">
                                    <button type="button" id="startBtn" class="btn btn-primary btn-lg">
                                        <span class="glyphicon glyphicon-play"></span> Generierung starten
                                    </button>
                                    <button type="button" id="cancelBtn" class="btn btn-danger btn-lg" style="display:none;">
                                        <span class="glyphicon glyphicon-stop"></span> Abbrechen
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div id="progressPanel" class="panel panel-info" style="display:none;">
                    <div class="panel-heading">
                        <h3 class="panel-title">Fortschritt</h3>
                    </div>
                    <div class="panel-body">
                        <div class="progress">
                            <div id="progressBar" class="progress-bar progress-bar-striped active" role="progressbar" style="width: 0%">
                                <span id="progressText">0%</span>
                            </div>
                        </div>
                        <div id="statusLog" class="well well-sm" style="height:300px; overflow-y:auto; font-family:monospace; font-size:12px;">
                        </div>
                    </div>
                </div>
                
                <div id="resultPanel" class="panel panel-success" style="display:none;">
                    <div class="panel-heading">
                        <h3 class="panel-title">Ergebnis</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-condensed">
                            <tbody>
                                <tr>
                                    <td><strong>Geprüft:</strong></td>
                                    <td id="result_checked">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Übersprungen (bereits lokal):</strong></td>
                                    <td id="result_skipped">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Generiert:</strong></td>
                                    <td id="result_generated">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Fehler:</strong></td>
                                    <td id="result_failed">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Aktualisiert in DB:</strong></td>
                                    <td id="result_updated">-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    var isRunning = false;
    
    $('#startBtn').click(function() {
        var limit = parseInt($('#limit').val()) || 10;
        var word_id = $('#word_id').val() ? parseInt($('#word_id').val()) : null;
        
        if (isRunning) {
            alert('Generierung bereits aktiv!');
            return;
        }
        
        isRunning = true;
        $('#startBtn').prop('disabled', true).hide();
        $('#cancelBtn').show().prop('disabled', false);
        $('#progressPanel').show();
        $('#resultPanel').hide();
        $('#statusLog').html('');
        
        syncImages(limit, word_id, 0);
    });
    
    $('#cancelBtn').click(function() {
        isRunning = false;
        $('#startBtn').prop('disabled', false).show();
        $('#cancelBtn').hide().prop('disabled', true);
        log('Generierung abgebrochen.');
    });
    
    function syncImages(limit, word_id, offset) {
        if (!isRunning) return;
        
        var url = '?action=generate_ajax&limit=' + limit;
        if (word_id) url += '&word_id=' + word_id;
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            timeout: 300000, // 5 Minuten
            success: function(data) {
                if (data.error) {
                    log('FEHLER: ' + data.error);
                    if (data.details && data.details.length) {
                        for (var i = 0; i < data.details.length; i++) {
                            log(data.details[i]);
                        }
                    }
                    showResult(data);
                } else {
                    log('Fertig!');
                    log('Geprüft: ' + data.checked);
                    log('Übersprungen: ' + data.skipped);
                    log('Generiert: ' + data.generated);
                    log('Fehler: ' + data.failed);
                    log('Aktualisiert: ' + data.updated);
                    if (data.details && data.details.length) {
                        for (var j = 0; j < data.details.length; j++) {
                            log(data.details[j]);
                        }
                    }
                    showResult(data);
                }
                
                isRunning = false;
                $('#startBtn').prop('disabled', false).show();
                $('#cancelBtn').hide().prop('disabled', true);
            },
            error: function(xhr, status, error) {
                log('AJAX-Fehler: ' + status + ' - ' + error);
                isRunning = false;
                $('#startBtn').prop('disabled', false).show();
                $('#cancelBtn').hide().prop('disabled', true);
            }
        });
    }
    
    function log(msg) {
        var timestamp = new Date().toLocaleTimeString();
        var html = '[' + timestamp + '] ' + escapeHtml(msg) + '<br>';
        $('#statusLog').append(html);
        $('#statusLog').scrollTop($('#statusLog')[0].scrollHeight);
    }
    
    function showResult(data) {
        $('#result_checked').text(data.checked || 0);
        $('#result_skipped').text(data.skipped || 0);
        $('#result_generated').text(data.generated || 0);
        $('#result_failed').text(data.failed || 0);
        $('#result_updated').text(data.updated || 0);
        $('#resultPanel').show();
    }
    
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>

<?php

function sync_images_batch($cf_account, $cf_token, $cf_model, $style_override, $limit, $specific_word_id) {
    $db = get_db_connection();
    $images_dir = __DIR__ . '/images';
    $reference_dir = __DIR__ . '/reference_img';
    $reference_images = load_reference_images($reference_dir);
    $resolved_model = resolve_image_model($cf_model, $reference_images);
    
    if (!is_dir($images_dir) && !mkdir($images_dir, 0775, true)) {
        return ['error' => 'images-Verzeichnis konnte nicht erstellt werden.'];
    }
    
    $stats = [
        'checked' => 0,
        'skipped' => 0,
        'generated' => 0,
        'failed' => 0,
        'updated' => 0,
        'error' => null,
        'details' => [],
        'model' => $resolved_model,
        'reference_images' => count($reference_images),
    ];

    if (!empty($reference_images)) {
        $stats['details'][] = 'Referenzbilder geladen: ' . count($reference_images) . ' Datei(en).';
        if (strpos($resolved_model, '@cf/black-forest-labs/flux-2-') !== 0) {
            $stats['details'][] = 'Hinweis: Referenzbilder werden nur mit FLUX-2 genutzt und fuer dieses Modell ignoriert.';
        }
    }
    $stats['details'][] = 'Verwendetes Modell: ' . $resolved_model;
    
    // Wörter ohne lokales Bild laden
    if ($specific_word_id) {
        $sql = "SELECT id, name, image FROM words WHERE id = " . (int)$specific_word_id;
    } else {
        $sql = "SELECT id, name, image FROM words WHERE (image IS NULL OR image = '') LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($db, $sql);
    if (!$result) {
        return ['error' => 'Datenbankfehler: ' . mysqli_error($db)];
    }
    
    $words = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $words[] = $row;
    }
    
    foreach ($words as $word) {
        $stats['checked']++;
        $word_id = (int)$word['id'];
        $word_name = $word['name'];
        $existing_image = $word['image'];
        
        // Prüfe ob lokales Bild existiert
        if ($existing_image && file_exists($images_dir . '/' . $existing_image)) {
            $stats['skipped']++;
            continue;
        }
        
        // Prompt bauen
        $prompt = build_word_prompt_web($word, $style_override, $db);
        $prompt_preview = preg_replace('/\s+/', ' ', trim($prompt));
        $stats['details'][] = 'Wort #' . $word_id . ' (' . $word_name . '): Prompt-Laenge ' . strlen($prompt) . ', Vorschau: ' . substr($prompt_preview, 0, 180) . (strlen($prompt_preview) > 180 ? '...' : '');
        
        // Bild generieren
        $image_result = generate_image_cloudflare($cf_account, $cf_token, $resolved_model, $prompt, $reference_images);
        if (!$image_result['ok']) {
            $stats['failed']++;
            $stats['details'][] = 'Wort #' . $word_id . ' (' . $word_name . '): ' . $image_result['error'];
            continue;
        }
        
        // Datei speichern
        $filename = save_image_file($images_dir, $word_id, $word_name, $image_result['image_data']);
        if (!$filename) {
            $stats['failed']++;
            $stats['details'][] = 'Wort #' . $word_id . ' (' . $word_name . '): Bild konnte nicht gespeichert werden.';
            continue;
        }
        
        $stats['generated']++;
        
        // DB aktualisieren
        $update_sql = "UPDATE words SET image = '" . mysqli_real_escape_string($db, $filename) . "' WHERE id = " . $word_id;
        if (mysqli_query($db, $update_sql)) {
            $stats['updated']++;
        }
    }
    
    mysqli_close($db);
    return $stats;
}

function resolve_image_model($configured_model, $reference_images) {
    $default_text_model = '@cf/black-forest-labs/flux-1-schnell';
    $default_reference_model = '@cf/black-forest-labs/flux-2-dev';

    if ($configured_model !== '') {
        return $configured_model;
    }

    if (!empty($reference_images)) {
        return $default_reference_model;
    }

    return $default_text_model;
}

function load_reference_images($reference_dir) {
    if (!is_dir($reference_dir)) {
        return [];
    }

    $patterns = ['*.png', '*.jpg', '*.jpeg', '*.webp'];
    $files = [];
    foreach ($patterns as $pattern) {
        $matched = glob($reference_dir . DIRECTORY_SEPARATOR . $pattern);
        if ($matched !== false) {
            $files = array_merge($files, $matched);
        }
    }

    sort($files, SORT_NATURAL | SORT_FLAG_CASE);
    return array_slice(array_values(array_unique($files)), 0, 4);
}

function build_word_prompt_web($word, $style_override, $db) {
    $parts = [];
    $parts[] = 'Erstelle eine klare, didaktische Illustration fuer eine Wortbildkarte fuer Kinder.';
    $parts[] = 'Das Zielwort ist "' . $word['name'] . '".';
    
    if ($style_override !== '') {
        $parts[] = $style_override;
    } else {
        $parts[] = 'Style: a single clean children\'s educational illustration, like the provided reference images: hand-drawn cartoon look, thick dark outlines, soft shading, slightly textured flat colors, friendly and clear, isolated on a pure white background.';
        $parts[] = 'Show exactly one large central motif only, centered on the canvas, filling most of the image, with no scene or background context.';
        $parts[] = 'The motif must fit the everyday life and cultural environment of a child growing up in Switzerland (Swiss-German-speaking region), so a Swiss child would instantly recognize it.';
        $parts[] = 'Avoid culturally specific elements from other countries that a Swiss child would not know.';
        $parts[] = 'No text anywhere in the image, no title, no caption, no letters, no numbers, no labels, no logo, no watermark, no frame, no collage, no extra decorative icons, no unrelated objects, no animals, no food, no planets, no emojis, no second motif.';
        $parts[] = 'Do not render the word itself in the image.';
        $parts[] = 'Keep the composition minimal and uncluttered, with a plain white background and only the target object.';
    }
    
    return implode(' ', $parts);
}

function generate_image_cloudflare($account_id, $api_token, $model, $prompt, $reference_images = []) {
    $url = 'https://api.cloudflare.com/client/v4/accounts/' . rawurlencode($account_id) . '/ai/run/' . $model;

    $supports_reference_images = strpos($model, '@cf/black-forest-labs/flux-2-') === 0;
    $use_multipart = $supports_reference_images && !empty($reference_images);

    $ch = curl_init($url);
    $headers = [
        'Authorization: Bearer ' . $api_token,
    ];

    if ($use_multipart) {
        $postfields = [
            'prompt' => $prompt,
            'width' => '1024',
            'height' => '1024',
            'steps' => '25',
        ];

        $temp_files = [];
        foreach ($reference_images as $index => $reference_image) {
            $prepared = prepare_reference_image($reference_image);
            if (!$prepared['ok']) {
                return ['ok' => false, 'error' => $prepared['error']];
            }

            $temp_files[] = $prepared['temp_file'];
            $postfields['input_image_' . $index] = new CURLFile($prepared['temp_file'], 'image/jpeg', basename($reference_image));
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 180,
        ]);
    } else {
        $payload = [
            'prompt' => $prompt,
            'num_steps' => 4,
        ];

        $headers[] = 'Content-Type: application/json';

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 180,
        ]);
        $temp_files = [];
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    foreach ($temp_files as $temp_file) {
        if (is_string($temp_file) && file_exists($temp_file)) {
            @unlink($temp_file);
        }
    }
    
    if ($curl_error) {
        error_log("Cloudflare cURL error: $curl_error");
        return ['ok' => false, 'error' => 'cURL-Fehler: ' . $curl_error];
    }
    
    if ($http_code !== 200) {
        error_log("Cloudflare API error ($http_code): $response");
        $message = 'Cloudflare API Fehler (' . $http_code . ')';
        $json = json_decode($response, true);
        if (is_array($json) && isset($json['errors'][0]['message'])) {
            $message .= ': ' . $json['errors'][0]['message'];
        } elseif (is_string($response) && $response !== '') {
            $message .= ': ' . substr($response, 0, 500);
        }
        return ['ok' => false, 'error' => $message];
    }
    
    $json = json_decode($response, true);
    if (!isset($json['result']['image'])) {
        error_log("Invalid Cloudflare response: " . substr($response, 0, 500));
        $message = 'Unerwartete Cloudflare-Antwort';
        if (isset($json['errors'][0]['message'])) {
            $message .= ': ' . $json['errors'][0]['message'];
        }
        return ['ok' => false, 'error' => $message];
    }
    
    $image_base64 = $json['result']['image'];
    $decoded = base64_decode($image_base64);
    if ($decoded === false) {
        error_log("Failed to decode base64 image data");
        return ['ok' => false, 'error' => 'Bilddaten konnten nicht dekodiert werden'];
    }
    
    return ['ok' => true, 'image_data' => $decoded];
}

function prepare_reference_image($source_path) {
    if (!function_exists('imagecreatefromstring') || !function_exists('imagecreatetruecolor')) {
        return ['ok' => false, 'error' => 'GD-Erweiterung fehlt, Referenzbilder können nicht verkleinert werden.'];
    }

    $image_data = @file_get_contents($source_path);
    if ($image_data === false) {
        return ['ok' => false, 'error' => 'Referenzbild konnte nicht gelesen werden: ' . basename($source_path)];
    }

    $source_image = @imagecreatefromstring($image_data);
    if ($source_image === false) {
        return ['ok' => false, 'error' => 'Referenzbildformat wird nicht unterstützt: ' . basename($source_path)];
    }

    $source_width = imagesx($source_image);
    $source_height = imagesy($source_image);
    $max_size = 512;
    $scale = min($max_size / $source_width, $max_size / $source_height, 1);
    $target_width = max(1, (int) round($source_width * $scale));
    $target_height = max(1, (int) round($source_height * $scale));

    $target_image = imagecreatetruecolor($target_width, $target_height);
    $white = imagecolorallocate($target_image, 255, 255, 255);
    imagefilledrectangle($target_image, 0, 0, $target_width, $target_height, $white);
    imagecopyresampled($target_image, $source_image, 0, 0, 0, 0, $target_width, $target_height, $source_width, $source_height);

    $temp_file = tempnam(sys_get_temp_dir(), 'wordlab_ref_');
    if ($temp_file === false) {
        imagedestroy($source_image);
        imagedestroy($target_image);
        return ['ok' => false, 'error' => 'Temporäre Datei für Referenzbild konnte nicht erstellt werden.'];
    }

    $jpeg_file = $temp_file . '.jpg';
    if (!imagejpeg($target_image, $jpeg_file, 92)) {
        @unlink($temp_file);
        imagedestroy($source_image);
        imagedestroy($target_image);
        return ['ok' => false, 'error' => 'Referenzbild konnte nicht vorbereitet werden: ' . basename($source_path)];
    }

    @unlink($temp_file);
    imagedestroy($source_image);
    imagedestroy($target_image);

    return ['ok' => true, 'temp_file' => $jpeg_file];
}

function save_image_file($images_dir, $word_id, $word_name, $image_data) {
    $slug = slugify($word_name);
    $random = bin2hex(random_bytes(4));
    $filename = 'word_' . $word_id . '_' . $slug . '_' . $random . '.jpg';
    $filepath = $images_dir . '/' . $filename;
    
    if (file_put_contents($filepath, $image_data) === false) {
        error_log("Failed to save image: $filepath");
        return null;
    }
    
    return $filename;
}

function slugify($text) {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\w\s-]/u', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-');
}

?>
