<?php
session_start();

require_once('system/data.php');
require_once('system/security.php');

// Token und Tage-Parameter prüfen
if (!isset($_GET['token']) || !isset($_GET['job_id'])) {
    header("Location:jobs.php");
    exit();
}

$token = filter_data($_GET['token']);
$job_id = filter_data($_GET['job_id']);
$renewal_days = isset($_GET['days']) ? intval($_GET['days']) : 30;

// Job abrufen
$job = get_single_record('jobs', $job_id);

if (!$job) {
    $_SESSION['error'] = "Stelle nicht gefunden.";
    header("Location:jobs.php");
    exit();
}

// Token validieren
if ($job['renewal_token'] !== $token) {
    $_SESSION['error'] = "Token ungültig. Die Erneuerung konnte nicht durchgeführt werden.";
    header("Location:jobs.php");
    exit();
}

// Stelle ist nicht genehmigt
if ($job['status'] !== 'approved') {
    $_SESSION['error'] = "Diese Stelle ist nicht aktiv.";
    header("Location:jobs.php");
    exit();
}

// Stelle erneuern
$new_valid_until = date('Y-m-d', strtotime("+$renewal_days days"));

// Maximal-Check: nicht mehr als 12 Monate von heute
$max_date = date('Y-m-d', strtotime("+365 days"));
if (strtotime($new_valid_until) > strtotime($max_date)) {
    $new_valid_until = $max_date;
}

$sql = "UPDATE jobs 
        SET valid_until = '" . $new_valid_until . "', 
            last_reminder_sent = NULL,
            renewal_token = NULL 
        WHERE id = '" . $job_id . "'";

get_result($sql);

// Erfolgs-E-Mail an User
$user = get_single_record('user', $job['creator_id']);
if ($user) {
    $to = $user['email'];
    $subject = "Stellenanzeige erneuert - " . $job['name'];
    
    $message = "Liebe/r " . $user['firstname'] . " " . $user['lastname'] . ",\n\n";
    $message .= "Ihre Stellenanzeige wurde erfolgreich erneuert.\n\n";
    $message .= "Stelle: " . $job['name'] . "\n";
    $message .= "Gültig bis: " . date('d.m.Y', strtotime($new_valid_until)) . "\n\n";
    $message .= "Die Anzeige läuft bis zum angegebenen Datum und wird danach automatisch gelöscht.\n";
    $message .= "Etwa eine Woche vorher erhalten Sie erneut eine Erinnerung.\n\n";
    $message .= "Viele Grüsse,\nWortlab Team";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
    
    mail($to, $subject, $message, $headers);
}

$_SESSION['success'] = "Ihre Stellenanzeige wurde erfolgreich bis " . date('d.m.Y', strtotime($new_valid_until)) . " verlängert.";
header("Location:jobs.php?success=renewed");
exit();
?>
