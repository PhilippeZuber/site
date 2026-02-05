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

require_once('system/data.php');
require_once('system/security.php');

$page = 'newsletter';
$success_message = '';
$error_message = '';

// Newsletter versenden
if (isset($_POST['send_newsletter'])) {
    // Hole alle User mit Newsletter-Opt-in
    $sql = "SELECT user_id, email, firstname, lastname FROM user WHERE news = 'on'";
    $result = get_result($sql);
    
    $recipients = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $recipients[] = $row;
    }
    
    if (count($recipients) > 0) {
        $unsubscribe_secret = 'wortlab_unsub_v1';
        $subject = "Neu bei WORTLAB: Memory-Spiel 🎮";
        
        // Newsletter-Inhalt (HTML)
        $newsletter_content = file_get_contents('newsletter_memory.html');
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        $sent_count = 0;
        foreach ($recipients as $recipient) {
            $to = $recipient['email'];
            $token = hash('sha256', $recipient['user_id'] . '|' . $recipient['email'] . '|' . $unsubscribe_secret);
            $unsubscribe_url = 'https://wortlab.ch/unsubscribe.php?uid=' . $recipient['user_id'] . '&token=' . $token;
            $personalized_content = str_replace('Liebe WORTLAB-Nutzer/innen und -Nutzer', 
                                                'Liebe/r ' . $recipient['firstname'] . ' ' . $recipient['lastname'], 
                                                $newsletter_content);
            $personalized_content = str_replace('{{UNSUBSCRIBE_URL}}', $unsubscribe_url, $personalized_content);
            
            if (mail($to, $subject, $personalized_content, $headers)) {
                $sent_count++;
            }
        }
        
        $success_message = "Newsletter erfolgreich an $sent_count Empfänger versendet!";
    } else {
        $error_message = "Keine Newsletter-Abonnenten gefunden.";
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
                                            <p><strong>Versandrate:</strong> <?php echo $total_users > 0 ? round(($newsletter_users / $total_users) * 100, 1) : 0; ?>%</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="panel panel-info">
                                        <div class="panel-heading">
                                            <h3 class="panel-title">Newsletter-Vorschau</h3>
                                        </div>
                                        <div class="panel-body">
                                            <p><strong>Betreff:</strong> Neu bei WORTLAB: Memory-Spiel 🎮</p>
                                            <p><strong>Inhalt:</strong> Promotion der neuen Memory-Funktion</p>
                                            <p><strong>HTML-Vorlage:</strong> newsletter_memory.html</p>
                                            <br>
                                            <a href="newsletter_memory.html" target="_blank" class="btn btn-info btn-sm">
                                                <span class="glyphicon glyphicon-eye-open"></span> Vorschau anzeigen
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
                                        <li>Der Newsletter wird an <strong><?php echo $newsletter_users; ?> Empfänger</strong> versendet</li>
                                        <li>Nur Benutzer, die Newsletter abonniert haben (news = 'on'), erhalten die E-Mail</li>
                                        <li>Der Versand kann einige Minuten dauern</li>
                                        <li>Stellen Sie sicher, dass die Datei <code>newsletter_memory.html</code> existiert</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <form method="POST" action="">
                                <div class="form-group">
                                    <button type="submit" name="send_newsletter" class="btn btn-primary btn-lg" 
                                            onclick="return confirm('Möchten Sie den Newsletter wirklich an <?php echo $newsletter_users; ?> Empfänger versenden?');">
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
                                    <p>Senden Sie zunächst einen Test-Newsletter an sich selbst:</p>
                                    <form method="POST" action="" class="form-inline">
                                        <div class="form-group">
                                            <input type="email" name="test_email" class="form-control" placeholder="Ihre E-Mail" required>
                                        </div>
                                        <button type="submit" name="send_test" class="btn btn-success">
                                            <span class="glyphicon glyphicon-envelope"></span> Test senden
                                        </button>
                                    </form>
                                    <?php
                                    if (isset($_POST['send_test'])) {
                                        $test_email = filter_data($_POST['test_email']);
                                        $subject = "[TEST] Neu bei WORTLAB: Memory-Spiel 🎮";
                                        $newsletter_content = file_get_contents('newsletter_memory.html');
                                        $newsletter_content = str_replace('{{UNSUBSCRIBE_URL}}', 'https://wortlab.ch/unsubscribe.php', $newsletter_content);
                                        
                                        $headers = "MIME-Version: 1.0\r\n";
                                        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                                        $headers .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
                                        
                                        if (mail($test_email, $subject, $newsletter_content, $headers)) {
                                            echo '<div class="alert alert-success" style="margin-top: 15px;">Test-Newsletter an ' . htmlspecialchars($test_email) . ' versendet!</div>';
                                        } else {
                                            echo '<div class="alert alert-danger" style="margin-top: 15px;">Fehler beim Versand des Test-Newsletters.</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include './footer.php'; ?>
    </body>
</html>
