<?php
/*
    Cron-Job zur Verwaltung von Stellenablauf und Erinnerungen
    Ausführung: täglich um 1:00 Uhr
    URL: https://wortlab.ch/check_job_expiry.php
*/

require_once('system/data.php');
require_once('system/security.php');

// Sicherheit: nur von CLI oder mit Secret Token
$secret_token = 'wortlab_cron_secret_2026'; // In Produktion in .env Datei!
$is_valid = false;

// Prüfe CLI
if (php_sapi_name() === 'cli') {
    $is_valid = true;
}

// Prüfe Token
if (isset($_GET['token']) && $_GET['token'] === $secret_token) {
    $is_valid = true;
}

if (!$is_valid) {
    http_response_code(403);
    die("Forbidden");
}

// ========================================
// 1. Stellen die zu erneuern sind (7 Tage vor Ablauf)
// ========================================

$jobs_to_renew = get_jobs_needing_renewal();

foreach ($jobs_to_renew as $job) {
    $user = get_single_record('user', $job['creator_id']);
    
    if ($user && !empty($user['email'])) {
        // Token generieren
        $renewal_token = generate_job_token();
        
        // Token in DB speichern
        $sql = "UPDATE jobs SET renewal_token = '" . $renewal_token . "' WHERE id = '" . $job['id'] . "'";
        get_result($sql);
        
        // E-Mail vorbereiten
        $renewal_url = "https://wortlab.ch/renew_job.php?job_id=" . $job['id'] . "&token=" . $renewal_token . "&days=30";
        
        $to = $user['email'];
        $subject = "Stellenanzeige läuft demnächst ab - Bitte bestätigen";
        
        $message = "Liebe/r " . $user['firstname'] . " " . $user['lastname'] . ",\n\n";
        $message .= "Ihre Stellenanzeige läuft bis " . date('d.m.Y', strtotime($job['valid_until'])) . " ab.\n\n";
        $message .= "Stelle: " . $job['name'] . "\n\n";
        $message .= "Möchten Sie die Anzeige um weitere 30 Tage verlängern?\n\n";
        $message .= "Klicken Sie hier zur Erneuerung:\n";
        $message .= $renewal_url . "\n\n";
        $message .= "Falls Sie diesen Link nicht bestätigen, wird die Anzeige nach dem Ablaufdatum automatisch gelöscht.\n\n";
        $message .= "Viele Grüsse,\nWortlab Team";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        // E-Mail versenden
        if (mail($to, $subject, $message, $headers)) {
            // Markiere als gesendet
            $sql_update = "UPDATE jobs SET last_reminder_sent = CURDATE() WHERE id = '" . $job['id'] . "'";
            get_result($sql_update);
            
            echo "✓ Erinnerungs-E-Mail für Job " . $job['id'] . " an " . $user['email'] . " versendet.\n";
        } else {
            echo "✗ Fehler beim Versenden von E-Mail für Job " . $job['id'] . "\n";
        }
    }
}

// ========================================
// 2. Abgelaufene Stellen löschen
// ========================================

$expired_jobs = get_expired_jobs();

foreach ($expired_jobs as $expired) {
    $job = get_single_record('jobs', $expired['id']);
    
    if ($job) {
        $user = get_single_record('user', $job['creator_id']);
        
        // Stelle löschen
        expire_job($expired['id']);
        
        // E-Mail an User (Information)
        if ($user && !empty($user['email'])) {
            $to = $user['email'];
            $subject = "Ihre Stellenanzeige wurde automatisch gelöscht";
            
            $message = "Liebe/r " . $user['firstname'] . " " . $user['lastname'] . ",\n\n";
            $message .= "Ihre Stellenanzeige \"" . $job['name'] . "\" ist abgelaufen und wurde automatisch gelöscht.\n\n";
            $message .= "Sie können jederzeit eine neue Stellenanzeige aufgeben.\n\n";
            $message .= "Viele Grüsse,\nWortlab Team";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
            
            mail($to, $subject, $message, $headers);
        }
        
        echo "✓ Stelle gelöscht: Job " . $expired['id'] . " von User " . $job['creator_id'] . "\n";
    }
}

echo "\nCron-Job abgeschlossen: " . date('Y-m-d H:i:s') . "\n";
?>
