<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
    exit;
}
// Nur Admins dürfen Newsletter versenden
if ($_SESSION['role'] != 1) {
    header("Location:index.php");
    exit;
}

$user_id = $_SESSION['id'];

require_once('system/data.php');
require_once('system/security.php');

// AJAX: Empfängeranzahl dynamisch ermitteln (wird von JavaScript aufgerufen)
if (isset($_GET['action']) && $_GET['action'] === 'get_count' && isset($_SESSION['role']) && $_SESSION['role'] == 1) {
    $mode = isset($_GET['mode']) ? filter_data($_GET['mode']) : 'users';
    if (!in_array($mode, array('users', 'jobs', 'both'))) { $mode = 'users'; }
    $status = isset($_GET['job_status']) ? filter_data($_GET['job_status']) : 'both';
    if (!in_array($status, array('new', 'contacted', 'both'))) { $status = 'both'; }
    header('Content-Type: application/json');
    echo json_encode(array('count' => count(collect_newsletter_recipients($mode, $status))));
    exit;
}

$page = 'newsletter';
$success_message = '';
$error_message = '';

$unsubscribe_secret_user = 'wortlab_unsub_v1';
$unsubscribe_secret_job = 'wortlab_job_unsub_v1';
$recipient_mode = 'users';
$job_status_filter = 'both';

ensure_newsletter_send_log_table();

function build_newsletter_unsubscribe_token($type, $id, $email, $secret) {
    return hash('sha256', $type . '|' . $id . '|' . $email . '|' . $secret);
}

function get_newsletter_template_for_recipient($recipient_type) {
    if ($recipient_type === 'job_contact') {
        return 'newsletter_jobs.html';
    }
    return 'newsletter_memory.html';
}

function get_newsletter_subject_for_recipient($recipient_type, $is_test = false) {
    if ($recipient_type === 'job_contact') {
        $subject = 'WORTLAB Stellenplattform für Ihre Ausschreibung';
    } else {
        $subject = 'Neu bei WORTLAB: Memory-Spiel';
    }

    if ($is_test) {
        return '[TEST] ' . $subject;
    }

    return $subject;
}

function build_newsletter_content($template_content, $salutation, $unsubscribe_url) {
    $personalized_content = str_replace('{{GREETING}}', $salutation, $template_content);
    $personalized_content = str_replace('Liebe WORTLAB-Nutzer/innen und -Nutzer', $salutation, $personalized_content);
    $personalized_content = str_replace('{{UNSUBSCRIBE_URL}}', $unsubscribe_url, $personalized_content);
    return $personalized_content;
}

function collect_newsletter_recipients($mode, $job_status = 'both') {
    $recipients = array();

    if ($mode === 'users' || $mode === 'both') {
        $sql_users = "SELECT user_id, email, firstname, lastname FROM user WHERE news = 'on' AND email <> ''";
        $result_users = get_result($sql_users);
        if ($result_users) {
            while ($row = mysqli_fetch_assoc($result_users)) {
                $email_key = strtolower(trim($row['email']));
                if ($email_key === '' || isset($recipients[$email_key])) {
                    continue;
                }
                $recipients[$email_key] = array(
                    'type' => 'user',
                    'id' => intval($row['user_id']),
                    'email' => trim($row['email']),
                    'firstname' => $row['firstname'],
                    'lastname' => $row['lastname']
                );
            }
        }
    }

    if ($mode === 'jobs' || $mode === 'both') {
        if ($job_status === 'new') {
            $status_clause = "status = 'new'";
        } elseif ($job_status === 'contacted') {
            $status_clause = "status = 'contacted'";
        } else {
            $status_clause = "status IN ('new','contacted')";
        }
        $sql_jobs = "SELECT id, email FROM job_pdf_contacts WHERE $status_clause AND email <> '' ORDER BY id DESC";
        $result_jobs = get_result($sql_jobs);
        if ($result_jobs) {
            while ($row = mysqli_fetch_assoc($result_jobs)) {
                $email_key = strtolower(trim($row['email']));
                if ($email_key === '' || isset($recipients[$email_key])) {
                    continue;
                }
                $recipients[$email_key] = array(
                    'type' => 'job_contact',
                    'id' => intval($row['id']),
                    'email' => trim($row['email']),
                    'firstname' => '',
                    'lastname' => ''
                );
            }
        }
    }

    return array_values($recipients);
}

// Newsletter versenden
if (isset($_POST['send_newsletter'])) {
    $recipient_mode = isset($_POST['recipient_mode']) ? filter_data($_POST['recipient_mode']) : 'users';
    if (!in_array($recipient_mode, array('users', 'jobs', 'both'))) {
        $recipient_mode = 'users';
    }

    $job_status_filter = isset($_POST['job_status_filter']) ? filter_data($_POST['job_status_filter']) : 'both';
    if (!in_array($job_status_filter, array('new', 'contacted', 'both'))) {
        $job_status_filter = 'both';
    }

    $recipients = collect_newsletter_recipients($recipient_mode, $job_status_filter);
    
    if (count($recipients) > 0) {
        $template_cache = array();
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        $sent_count = 0;
        $sent_users = 0;
        $sent_job_contacts = 0;
        $send_delay_us = 600000;
        foreach ($recipients as $recipient) {
            $to = $recipient['email'];
            $recipient_ref_id = intval($recipient['id']);
            $recipient_type = $recipient['type'];
            $template_file = get_newsletter_template_for_recipient($recipient_type);

            if (!isset($template_cache[$template_file])) {
                $template_cache[$template_file] = file_get_contents($template_file);
            }

            $template_content = $template_cache[$template_file];
            if ($template_content === false) {
                $error_message = "Die HTML-Vorlage " . $template_file . " konnte nicht geladen werden.";
                add_newsletter_send_log($user_id, $to, $recipient_type, $recipient_ref_id, $recipient_mode, get_newsletter_subject_for_recipient($recipient_type), $template_file, 0, $error_message);
                continue;
            }

            $subject = get_newsletter_subject_for_recipient($recipient_type);

            if ($recipient_type === 'user') {
                $token = build_newsletter_unsubscribe_token('user', $recipient_ref_id, $recipient['email'], $unsubscribe_secret_user);
                $unsubscribe_url = 'https://wortlab.ch/unsubscribe.php?uid=' . $recipient_ref_id . '&token=' . $token;
                $full_name = trim($recipient['firstname'] . ' ' . $recipient['lastname']);
                $salutation = ($full_name !== '') ? ('Liebe/r ' . $full_name) : 'Liebe WORTLAB-Nutzerin, lieber WORTLAB-Nutzer';
            } else {
                $token = build_newsletter_unsubscribe_token('job_contact', $recipient_ref_id, $recipient['email'], $unsubscribe_secret_job);
                $unsubscribe_url = 'https://wortlab.ch/unsubscribe.php?cid=' . $recipient_ref_id . '&type=job_contact&token=' . $token;
                $salutation = 'Guten Tag';
            }

            $personalized_content = build_newsletter_content($template_content, $salutation, $unsubscribe_url);

            $mail_ok = mail($to, $subject, $personalized_content, $headers);
                add_newsletter_send_log($user_id, $to, $recipient_type, $recipient_ref_id, $recipient_mode, $subject, $template_file, $mail_ok ? 1 : 0, $mail_ok ? '' : 'mail() hat false zurückgegeben');

            if ($mail_ok) {
                $sent_count++;
                if ($recipient_type === 'user') {
                    $sent_users++;
                } else {
                    $sent_job_contacts++;
                    $contact_id = $recipient_ref_id;
                    $sql_mark_contacted = "UPDATE job_pdf_contacts SET status = 'contacted', last_seen_at = NOW() WHERE id = $contact_id";
                    get_result($sql_mark_contacted);
                }
            }

            usleep($send_delay_us);
        }

        if ($error_message === '') {
            $success_message = "Newsletter erfolgreich an $sent_count Empfänger versendet (Benutzer: $sent_users, Stellenkontakte: $sent_job_contacts).";
        }
    } else {
        $error_message = "Keine Empfänger für die gewählte Zielgruppe gefunden.";
    }
}

if (isset($_POST['send_test']) && isset($_POST['recipient_mode'])) {
    $recipient_mode = filter_data($_POST['recipient_mode']);
    if (!in_array($recipient_mode, array('users', 'jobs', 'both'))) {
        $recipient_mode = 'users';
    }
}

// Statistik abrufen
$sql_total = "SELECT COUNT(*) as total FROM user";
$total_result = get_result($sql_total);
$total_row = mysqli_fetch_assoc($total_result);
$total_users = $total_row['total'];

$sql_newsletter = "SELECT COUNT(*) as total FROM user WHERE news = 'on'";
$newsletter_result = get_result($sql_newsletter);
$newsletter_row = mysqli_fetch_assoc($newsletter_result);
$newsletter_users = $newsletter_row['total'];

$sql_job_total = "SELECT COUNT(*) as total FROM job_pdf_contacts";
$job_total_result = get_result($sql_job_total);
$job_total_row = mysqli_fetch_assoc($job_total_result);
$job_total = $job_total_row ? intval($job_total_row['total']) : 0;

$sql_job_mailable = "SELECT COUNT(*) as total FROM job_pdf_contacts WHERE status IN ('new','contacted')";
$job_mailable_result = get_result($sql_job_mailable);
$job_mailable_row = mysqli_fetch_assoc($job_mailable_result);
$job_mailable = $job_mailable_row ? intval($job_mailable_row['total']) : 0;

$recipient_mode = isset($_POST['recipient_mode']) ? filter_data($_POST['recipient_mode']) : $recipient_mode;
if (!in_array($recipient_mode, array('users', 'jobs', 'both'))) { $recipient_mode = 'users'; }
$job_status_filter = isset($_POST['job_status_filter']) ? filter_data($_POST['job_status_filter']) : 'both';
if (!in_array($job_status_filter, array('new', 'contacted', 'both'))) {
    $job_status_filter = 'both';
}

$estimated_recipients = count(collect_newsletter_recipients($recipient_mode, $job_status_filter));

$log_entries = array();
$sql_logs = "SELECT sent_at, recipient_email, recipient_type, recipient_mode, subject, template_file, success, error_message FROM newsletter_send_log ORDER BY id DESC LIMIT 100";
$result_logs = get_result($sql_logs);
if ($result_logs) {
    while ($row = mysqli_fetch_assoc($result_logs)) {
        $log_entries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
    <?php include './header.php'; ?>
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <h1>Newsletter versenden</h1>
                            <p class="lead">Memory-Funktion Newsletter</p>
                            
                            <?php if ($success_message): ?>
                                <div class="alert alert-success">
                                    <strong>Erfolg!</strong> <?php echo $success_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger">
                                    <strong>Fehler!</strong> <?php echo $error_message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row" style="margin-bottom: 30px;">
                                <div class="col-md-4">
                                    <div class="panel panel-primary">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Statistik</h3>
                                        </div>
                                        <div class="panel-body">
                                            <p><strong>Gesamt Benutzer:</strong> <?php echo $total_users; ?></p>
                                            <p><strong>Newsletter-Abonnenten:</strong> <?php echo $newsletter_users; ?></p>
                                            <p><strong>PDF-Kontakte total:</strong> <?php echo $job_total; ?></p>
                                            <p><strong>PDF-Kontakte aktiv:</strong> <?php echo $job_mailable; ?></p>
                                            <p><strong>Aktuelle Zielgruppe:</strong> <?php echo htmlspecialchars($recipient_mode); ?></p>
                                            <p><strong>Schätzung Versand:</strong> <span id="estimated-count"><?php echo $estimated_recipients; ?></span> Empfänger</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Newsletter-Vorschau</h3>
                                        </div>
                                        <div class="panel-body">
                                            <p><strong>Betreff Benutzer:</strong> Neu bei WORTLAB: Memory-Spiel</p>
                                            <p><strong>Betreff Stellenkontakte:</strong> WORTLAB Stellenplattform für Ihre Ausschreibung</p>
                                            <p><strong>Vorlagen:</strong> newsletter_memory.html (Benutzer), newsletter_jobs.html (Stellenkontakte)</p>
                                            <br>
                                            <a href="newsletter_memory.html" target="_blank" class="btn btn-info btn-sm">
                                                <span class="glyphicon glyphicon-eye-open"></span> Vorschau Benutzer
                                            </a>
                                            <a href="newsletter_jobs.html" target="_blank" class="btn btn-default btn-sm">
                                                <span class="glyphicon glyphicon-eye-open"></span> Vorschau Stellenkontakte
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="panel panel-warning">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><span class="glyphicon glyphicon-warning-sign"></span> Wichtig vor dem Versand</h3>
                                </div>
                                <div class="panel-body">
                                    <ul>
                                        <li>Der Newsletter wird an <strong><span id="estimated-count-warning"><?php echo $estimated_recipients; ?></span> Empfänger</strong> versendet</li>
                                        <li>Duplikate werden per E-Mail-Adresse automatisch entfernt</li>
                                        <li>Bei Stellenkontakten werden nur Einträge mit dem gewählten Status berücksichtigt (aktuell: <strong><?php echo htmlspecialchars($job_status_filter); ?></strong>)</li>
                                        <li>Jeder Versand wird im Versand-Log protokolliert</li>
                                        <li>Der Versand kann einige Minuten dauern</li>
                                        <li>Stellen Sie sicher, dass die Dateien <code>newsletter_memory.html</code> und <code>newsletter_jobs.html</code> existieren</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="form-group" style="max-width: 420px;">
                                    <label for="recipient_mode">Empfängergruppe</label>
                                    <select class="form-control" id="recipient_mode" name="recipient_mode">
                                        <option value="users" <?php echo $recipient_mode === 'users' ? 'selected' : ''; ?>>Nur Wortlab-Benutzer (news = on)</option>
                                        <option value="jobs" <?php echo $recipient_mode === 'jobs' ? 'selected' : ''; ?>>Nur Stellen-Ausschreibende (job_pdf_contacts)</option>
                                        <option value="both" <?php echo $recipient_mode === 'both' ? 'selected' : ''; ?>>Beide Gruppen (mit Duplikat-Prüfung)</option>
                                    </select>
                                </div>
                                <div class="form-group" style="max-width: 420px;">
                                    <label for="job_status_filter">Status der Stellenkontakte</label>
                                    <select class="form-control" id="job_status_filter" name="job_status_filter">
                                        <option value="both" <?php echo $job_status_filter === 'both' ? 'selected' : ''; ?>>Alle aktiven (new + contacted)</option>
                                        <option value="new" <?php echo $job_status_filter === 'new' ? 'selected' : ''; ?>>Nur neue Kontakte (status = new)</option>
                                        <option value="contacted" <?php echo $job_status_filter === 'contacted' ? 'selected' : ''; ?>>Nur bereits Kontaktierte (status = contacted)</option>
                                    </select>
                                    <span class="help-block">Gilt nur für Stellenkontakte, nicht für Wortlab-Benutzer.</span>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="send_newsletter" class="btn btn-primary btn-lg" 
                                            onclick="return confirm('Möchten Sie den Newsletter wirklich an ' + document.getElementById('estimated-count').textContent + ' Empfänger versenden?');">  
                                        <span class="glyphicon glyphicon-send"></span> Newsletter jetzt versenden
                                    </button>
                                    <a href="index.php" class="btn btn-default btn-lg">
                                        <span class="glyphicon glyphicon-remove"></span> Abbrechen
                                    </a>
                                </div>
                            </form>
                            
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Test-Empfänger</h3>
                                </div>
                                <div class="panel-body">
                                    <p>Senden Sie Testmails für beide Vorlagen an eine beliebige Adresse:</p>
                                    <form method="POST" action="" class="form-inline">
                                        <div class="form-group">
                                            <input type="email" name="test_email" class="form-control" placeholder="Ihre E-Mail" required>
                                        </div>
                                        <button type="submit" name="send_test_user" class="btn btn-success">
                                            <span class="glyphicon glyphicon-envelope"></span> Test Benutzer-Mail
                                        </button>
                                        <button type="submit" name="send_test_job" class="btn btn-info">
                                            <span class="glyphicon glyphicon-briefcase"></span> Test Stellenkontakte-Mail
                                        </button>
                                    </form>
                                    <?php
                                    if (isset($_POST['send_test_user']) || isset($_POST['send_test_job'])) {
                                        $test_email = filter_data($_POST['test_email']);
                                        $test_recipient_type = isset($_POST['send_test_job']) ? 'job_contact' : 'user';
                                        $subject = get_newsletter_subject_for_recipient($test_recipient_type, true);
                                        $template_file = get_newsletter_template_for_recipient($test_recipient_type);
                                        $newsletter_content = file_get_contents($template_file);
                                        if ($newsletter_content !== false) {
                                            $newsletter_content = build_newsletter_content($newsletter_content, 'Guten Tag', 'https://wortlab.ch/unsubscribe.php');
                                        }
                                        
                                        $headers = "MIME-Version: 1.0\r\n";
                                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                                        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";

                                        if ($newsletter_content !== false && mail($test_email, $subject, $newsletter_content, $headers)) {
                                            add_newsletter_send_log($user_id, $test_email, 'test', null, 'test', $subject, $template_file, 1, '');
                                            echo '<div class="alert alert-success" style="margin-top: 15px;">Testmail (' . htmlspecialchars($template_file) . ') an ' . htmlspecialchars($test_email) . ' versendet!</div>';
                                        } else {
                                            add_newsletter_send_log($user_id, $test_email, 'test', null, 'test', $subject, $template_file, 0, 'Testversand fehlgeschlagen oder Vorlage nicht lesbar');
                                            echo '<div class="alert alert-danger" style="margin-top: 15px;">Fehler beim Versand der Testmail.</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Versand-Log (letzte 100 Einträge)</h3>
                                </div>
                                <div class="panel-body table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Zeitpunkt</th>
                                                <th>E-Mail</th>
                                                <th>Typ</th>
                                                <th>Modus</th>
                                                <th>Vorlage</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($log_entries)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-muted">Noch keine Versand-Logs vorhanden.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($log_entries as $entry): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($entry['sent_at']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['recipient_email']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['recipient_type']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['recipient_mode']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['template_file']); ?></td>
                                                        <td>
                                                            <?php if (intval($entry['success']) === 1): ?>
                                                                <span class="label label-success">OK</span>
                                                            <?php else: ?>
                                                                <span class="label label-danger">Fehler</span>
                                                                <?php if (!empty($entry['error_message'])): ?>
                                                                    <div class="text-danger" style="margin-top: 6px;"><?php echo htmlspecialchars($entry['error_message']); ?></div>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
        $(document).ready(function() {
            function updateEstimatedCount() {
                var mode = $('#recipient_mode').val();
                var status = $('#job_status_filter').val();
                $.getJSON(
                    'send_newsletter.php?action=get_count&mode=' + encodeURIComponent(mode) + '&job_status=' + encodeURIComponent(status),
                    function(data) {
                        $('#estimated-count, #estimated-count-warning').text(data.count);
                    }
                );
            }
            $('#recipient_mode, #job_status_filter').on('change', updateEstimatedCount);
        });
        </script>
        <?php include './footer.php'; ?>
    </body>
</html>
