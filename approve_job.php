<?php
session_start();

require_once('system/data.php');
require_once('system/security.php');

// Nur Admin darf Stellen freigeben
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location:login.php");
    exit();
}

// Parameter prüfen
if (!isset($_GET['token']) || !isset($_GET['job_id']) || !isset($_GET['action'])) {
    header("Location:approve_jobs.php");
    exit();
}

$token = filter_data($_GET['token']);
$job_id = filter_data($_GET['job_id']);
$action = filter_data($_GET['action']);

// Job abrufen
$job = get_single_record('jobs', $job_id);

if (!$job) {
    $_SESSION['error'] = "Stelle nicht gefunden.";
    header("Location:approve_jobs.php");
    exit();
}

// Token validieren
if ($job['approval_token'] !== $token) {
    $_SESSION['error'] = "Token ungültig. Die Freigabe konnte nicht durchgeführt werden.";
    header("Location:approve_jobs.php");
    exit();
}

// Job-Status prüfen (nur pending Jobs)
if ($job['status'] !== 'pending') {
    $_SESSION['error'] = "Diese Stelle ist nicht mehr im Freigabe-Status.";
    header("Location:approve_jobs.php");
    exit();
}

$user = get_single_record('user', $job['creator_id']);

// Aktion ausführen
if ($action === 'approve') {
    // Stelle genehmigen
    approve_job($job_id);
    
    // E-Mail an User
    if ($user) {
        $to = $user['email'];
        $subject = "Ihre Stellenanzeige wurde genehmigt - " . $job['name'];
        
        $message = "Liebe/r " . $user['firstname'] . " " . $user['lastname'] . ",\n\n";
        $message .= "Ihre Stellenanzeige wurde genehmigt und ist jetzt online.\n\n";
        $message .= "Stelle: " . $job['name'] . "\n";
        $message .= "Gültig bis: " . date('d.m.Y', strtotime($job['valid_until'])) . "\n\n";
        $message .= "Betrachten Sie die Anzeige auf unserer Plattform:\n";
        $message .= "https://wortlab.ch/jobs.php\n\n";
        $message .= "Viele Grüsse,\nWortlab Team";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        mail($to, $subject, $message, $headers);
    }
    
    $_SESSION['success'] = "Stelle genehmigt und User benachrichtigt.";
    
} elseif ($action === 'reject') {
    // Stelle ablehnen
    $reason = isset($_GET['reason']) ? filter_data($_GET['reason']) : "Keine Begründung angegeben.";
    
    // Job löschen
    delete_record('jobs', $job_id);
    
    // E-Mail an User
    if ($user) {
        $to = $user['email'];
        $subject = "Ihre Stellenanzeige wurde abgelehnt - " . $job['name'];
        
        $message = "Liebe/r " . $user['firstname'] . " " . $user['lastname'] . ",\n\n";
        $message .= "Leider konnte Ihre Stellenanzeige nicht genehmigt werden.\n\n";
        $message .= "Stelle: " . $job['name'] . "\n";
        $message .= "Grund: " . $reason . "\n\n";
        $message .= "Falls Sie Fragen haben, kontaktieren Sie uns bitte unter info@wortlab.ch\n\n";
        $message .= "Viele Grüsse,\nWortlab Team";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        mail($to, $subject, $message, $headers);
    }
    
    $_SESSION['success'] = "Stelle abgelehnt und User benachrichtigt.";
    
} else {
    $_SESSION['error'] = "Ungültige Aktion.";
}

header("Location:approve_jobs.php");
exit();
?>
