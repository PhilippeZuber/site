<?php
session_start();

require_once('system/data.php');
require_once('system/security.php');

$page = 'jobs';

// User-ID laden wenn eingeloggt
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
} else {
    $user_id = null;
}

// Stellenanzeige aufgeben - nur für eingeloggte User
if (isset($_POST['job-submit']) && $user_id !== null) {
    $name = filter_data($_POST['name']);
    $kanton = filter_data($_POST['kanton']);
    $institution = filter_data($_POST['institution']);
    $stellenantritt = filter_data($_POST['stellenantritt']);
    $erscheinen_am = filter_data($_POST['erscheinen_am']);
    $pdf_url = filter_data($_POST['pdf_url']);
    $validity_days = intval($_POST['validity_days']);
    
    // Validierung: Ablaufdatum maximal 365 Tage
    if ($validity_days > 365) {
        $validity_days = 365;
    }
    
    // PDF-URL Validierung - nur sichere .pdf URLs erlauben
    if (!empty($pdf_url)) {
        // Validiere mit strengem Regex: http(s):// + valide URL + .pdf
        if (!preg_match('/^https?:\/\/[a-zA-Z0-9\-._~:\/\?#\[\]@!$&\'()*+,;=%]+\.pdf$/i', $pdf_url)) {
            $_SESSION['error'] = "Ungültige PDF-URL. Bitte verwenden Sie eine direkte HTTPS-PDF-URL (z.B. https://example.com/dokument.pdf).";
            header("Location:jobs.php");
            exit();
        }
    }
    
    // Altersgruppe berechnen
    $valid_until = date('Y-m-d', strtotime("+$validity_days days"));
    
    // Trust-Level prüfen: hat User bereits eine freigegebene Stelle?
    $has_approved = user_has_approved_jobs($user_id);
    
    if ($has_approved) {
        // User hat bereits freigegebene Stelle → direkt approved
        $status = 'approved';
        $approval_token = null;
        $success_message = "Ihre Stellenanzeige ist jetzt online!";
    } else {
        // Neuer User → pending (Moderation erforderlich)
        $status = 'pending';
        $approval_token = generate_job_token();
        $success_message = "Ihre Stellenanzeige wurde eingereicht. Sie wird in Kürze überprüft.";
    }
    
    // Erneuerungs-Token generieren
    $renewal_token = generate_job_token();
    
    // Stellenanzeige in Datenbank speichern (echte Insert-ID benötigt)
    $sql = "INSERT INTO jobs (name, kanton, institution, stellenantritt, erscheinen_am, pdf_url, valid_until, renewal_token, status, approval_token, creator_id, created_at) VALUES (";
    $sql .= "'" . $name . "',";
    $sql .= "'" . $kanton . "',";
    $sql .= "'" . $institution . "',";
    $sql .= "'" . $stellenantritt . "',";
    $sql .= "'" . $erscheinen_am . "',";
    $sql .= "'" . $pdf_url . "',";
    $sql .= "'" . $valid_until . "',";
    $sql .= "'" . $renewal_token . "',";
    $sql .= "'" . $status . "',";
    $sql .= ($approval_token === null) ? "NULL," : "'" . $approval_token . "',";
    $sql .= "'" . intval($user_id) . "',";
    $sql .= "'" . date('Y-m-d H:i:s') . "')";

    $job_id = get_id_result($sql);

    if (!$job_id) {
        $_SESSION['error'] = "Die Stellenanzeige konnte nicht gespeichert werden. Bitte erneut versuchen.";
        header("Location:jobs.php");
        exit();
    }
    
    // E-Mails versenden
    $user_data = mysqli_fetch_assoc(get_user($user_id));
    
    if ($status === 'pending') {
        // E-Mail an Admin zur Moderation
        $to_admin = 'info@wortlab.ch';
        $subject_admin = "Neue Stellenanzeige zur Genehmigung: " . $name;
        
        $approve_url = "https://wortlab.ch/approve_job.php?job_id=" . $job_id . "&token=" . $approval_token . "&action=approve";
        $reject_url = "https://wortlab.ch/approve_job.php?job_id=" . $job_id . "&token=" . $approval_token . "&action=reject";
        
        $message_admin = "Neue Stellenanzeige zur Überprüfung:\n\n";
        $message_admin .= "Titel: " . $name . "\n";
        $message_admin .= "Institution: " . $institution . "\n";
        $message_admin .= "Kanton: " . $kanton . "\n";
        $message_admin .= "Stellenantritt: " . $stellenantritt . "\n";
        $message_admin .= "Gültig bis: " . date('d.m.Y', strtotime($valid_until)) . "\n";
        $message_admin .= "Einsender: " . $user_data['firstname'] . " " . $user_data['lastname'] . " (" . $user_data['email'] . ")\n\n";
        
        if (!empty($pdf_url)) {
            $message_admin .= "PDF-Link: " . $pdf_url . "\n\n";
        }
        
        $message_admin .= "--- FREIGABE ---\n";
        $message_admin .= "Genehmigen: " . $approve_url . "\n\n";
        $message_admin .= "Ablehnen: " . $reject_url . "\n";
        
        $headers_admin = "MIME-Version: 1.0\r\n";
        $headers_admin .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers_admin .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        mail($to_admin, $subject_admin, $message_admin, $headers_admin);
        
        // E-Mail an User
        $to_user = $user_data['email'];
        $subject_user = "Stellenanzeige eingereicht";
        
        $message_user = "Liebe/r " . $user_data['firstname'] . " " . $user_data['lastname'] . ",\n\n";
        $message_user .= "Ihre Stellenanzeige wurde eingereicht und wird in Kürze überprüft.\n\n";
        $message_user .= "Stelle: " . $name . "\n";
        $message_user .= "Gültig bis: " . date('d.m.Y', strtotime($valid_until)) . "\n\n";
        $message_user .= "Sie erhalten eine Benachrichtigung, sobald Ihre Anzeige genehmigt wurde.\n\n";
        $message_user .= "Viele Grüsse,\nWortlab Team";
        
        $headers_user = "MIME-Version: 1.0\r\n";
        $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers_user .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        mail($to_user, $subject_user, $message_user, $headers_user);
        
    } elseif ($status === 'approved') {
        // E-Mail an User - direkt online
        $to_user = $user_data['email'];
        $subject_user = "Ihre Stellenanzeige ist online";
        
        $message_user = "Liebe/r " . $user_data['firstname'] . " " . $user_data['lastname'] . ",\n\n";
        $message_user .= "Ihre Stellenanzeige ist jetzt online!\n\n";
        $message_user .= "Stelle: " . $name . "\n";
        $message_user .= "Gültig bis: " . date('d.m.Y', strtotime($valid_until)) . "\n\n";
        $message_user .= "Betrachten Sie die Anzeige auf unserer Plattform:\n";
        $message_user .= "https://wortlab.ch/jobs.php\n\n";
        $message_user .= "Die Anzeige läuft bis zum angegebenen Datum. Etwa eine Woche vorher erhalten Sie eine Erinnerung zur Verlängerung.\n\n";
        $message_user .= "Viele Grüsse,\nWortlab Team";
        
        $headers_user = "MIME-Version: 1.0\r\n";
        $headers_user .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers_user .= "From: WORTLAB <noreply@wortlab.ch>\r\n";
        
        mail($to_user, $subject_user, $message_user, $headers_user);
    }
    
    $_SESSION['success'] = $success_message;
    header("Location:jobs.php?success=1");
    exit();
}

// User-Daten für Modal-Vorausfüllung laden (nur wenn eingeloggt)
$user = null;
if ($user_id !== null) {
    $user = mysqli_fetch_assoc(get_user($user_id));
}

?>
<!DOCTYPE html>
<html lang="de">
    <?php include './header.php'; ?>
    <!--Modal window for jobs-->
    <div class="modal fade" id="JobsModal" tabindex="-1" role="dialog" aria-labelledby="jobs-wortlab">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="profil-wortlab">Stellenanzeige aufgeben</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Stellentitel / Institution <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="z.B. Logopädin/Logopäde 80% - Sprachheilschule Musterhausen" required>
                            <small class="form-text text-muted">Bitte geben Sie einen aussagekräftigen Titel ein.</small>
                        </div>
                        <div class="form-group">
                            <label for="institution">Institution <span style="color:red;">*</span></label>
                            <select class="form-control" id="institution" name="institution" required>
                                <option value="">-- Bitte wählen --</option>
                                <option value="schule">Schule</option>
                                <option value="klinik">Klinik</option>
                                <option value="praxis">Freie Praxis</option>
                                <option value="dienst">Logopäd. Dienst</option>
                                <option value="sonderschule">Sonderschule</option>
                                <option value="spd">SPD</option>
                                <option value="zentrum">Kompetenzzentrum</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kanton">Kanton <span style="color:red;">*</span></label>
                            <select class="form-control" id="kanton" name="kanton" required>
                                <option value="">-- Bitte wählen --</option>
                                <option value="ag">Aargau</option>
                                <option value="ar">Appenzell Ausserrhoden</option>
                                <option value="ai">Appenzell Innerrhoden</option>
                                <option value="bl">Basel-Landschaft</option>
                                <option value="bs">Basel-Stadt</option>
                                <option value="be">Bern</option>
                                <option value="fr">Freiburg</option>
                                <option value="ge">Genf</option>
                                <option value="gl">Glarus</option>
                                <option value="gr">Graubünden</option>
                                <option value="ju">Jura</option>
                                <option value="lu">Luzern</option>
                                <option value="ne">Neuenburg</option>
                                <option value="nw">Nidwalden</option>
                                <option value="ow">Obwalden</option>
                                <option value="sh">Schaffhausen</option>
                                <option value="sz">Schwyz</option>
                                <option value="so">Solothurn</option>
                                <option value="sg">St. Gallen</option>
                                <option value="ti">Tessin</option>
                                <option value="tg">Thurgau</option>
                                <option value="ur">Uri</option>
                                <option value="vd">Waadt</option>
                                <option value="vs">Wallis</option>
                                <option value="zg">Zug</option>
                                <option value="zh">Zürich</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stellenantritt">Stellenantritt <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="stellenantritt" name="stellenantritt" 
                                   placeholder="z.B. Ab sofort oder nach Vereinbarung" required>
                        </div>
                        <div class="form-group">
                            <label for="erscheinen_am">Erscheinen am</label>
                            <input type="date" class="form-control" id="erscheinen_am" name="erscheinen_am">
                        </div>
                        <div class="form-group">
                            <label for="pdf_url">Stellenanzeige (PDF-Link)</label>
                            <input type="url" class="form-control" id="pdf_url" name="pdf_url" 
                                   placeholder="z.B. https://example.com/stelle.pdf">
                            <small class="form-text text-muted">Bitte geben Sie nur eine URL zu einer PDF-Datei ein (z.B. https://example.com/stelle.pdf)</small>
                        </div>
                        <div class="form-group">
                            <label for="validity_days">Gültigkeitsdauer <span style="color:red;">*</span></label>
                            <select class="form-control" id="validity_days" name="validity_days" required>
                                <option value="">-- Bitte wählen --</option>
                                <option value="30">30 Tage</option>
                                <option value="60">60 Tage</option>
                                <option value="90">90 Tage</option>
                                <option value="180">6 Monate</option>
                                <option value="365">12 Monate (max.)</option>
                            </select>
                            <small class="form-text text-muted">Wie lange soll Ihre Anzeige online sein? Sie erhalten eine Erinnerung eine Woche vor Ablauf.</small>
                        </div>
                        <hr>
                        <p class="text-muted"><small><span style="color:red;">*</span> Pflichtfelder</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-success btn-sm" name="job-submit">Stellenanzeige aufgeben</button>
                    </div>
                </form>
            </div><!-- /modal content-->
        </div><!-- /modal dialog-->
    </div><!-- /modal window for jobs-->
    
    <!-- Info Modal für nicht eingeloggte Benutzer -->
    <div class="modal fade" id="LoginInfoModal" tabindex="-1" role="dialog" aria-labelledby="login-info">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="login-info">Einloggen erforderlich</h4>
                </div>
                <div class="modal-body">
                    <p><strong>Um eine Stellenanzeige aufzugeben, müssen Sie eingeloggt sein.</strong></p>
                    <p>Haben Sie bereits ein Konto? Loggen Sie sich jetzt ein:</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Abbrechen</button>
                    <a href="login.php" class="btn btn-primary btn-sm">
                        <span class="glyphicon glyphicon-log-in"></span> Zur Anmeldung
                    </a>
                </div>
            </div>
        </div>
    </div><!-- /modal window for login info-->
    <?php if($user_id === null): ?>
    <style>
        #JobsModal { display: none !important; }
    </style>
    <?php endif; ?>
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                	<div class="row">
                    	<div class="col-md-12">
                            <h1>Stellenangebote für Logopädinnen / Logopäden</h1>
                            <?php if(isset($_GET['success']) && $_GET['success'] == 1): ?>
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <strong>Erfolg!</strong> Ihre Stellenanzeige wurde erfolgreich aufgegeben.
                                </div>
                            <?php endif; ?>
                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <strong>Fehler:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row">
                    	<div class="col-md-12">
                    		<?php if($user_id !== null): ?>
                                <a href="#" data-toggle="modal" data-target="#JobsModal" class="btn btn-primary">
                                    <span class="glyphicon glyphicon-plus"></span> Stelle aufgeben
                                </a>
                    		<?php else: ?>
                                <a href="#" data-toggle="modal" data-target="#LoginInfoModal" class="btn btn-primary">
                                    <span class="glyphicon glyphicon-plus"></span> Stelle aufgeben
                                </a>
                    		<?php endif; ?>
                        </div>
                    </div>
                    <br>
                    <div class="row">
						<div class="col-md-3">    
                            <h2>Kanton</h2>
                            <select name="kanton" class="auswahl" id="kanton_filter">
                                <option value="" selected>- Alle -</option>
                                <option value="ag">Aargau</option>
                                <option value="ar">Appenzell Ausserrhoden</option>
                                <option value="ai">Appenzell Innerrhoden</option>
                                <option value="bl">Basel-Landschaft</option>
                                <option value="bs">Basel-Stadt</option>
                                <option value="be">Bern</option>
                                <option value="fr">Freiburg</option>
                                <option value="ge">Genf</option>
                                <option value="gl">Glarus</option>
                                <option value="gr">Graubünden</option>
                                <option value="ju">Jura</option>
                                <option value="lu">Luzern</option>
                                <option value="ne">Neuenburg</option>
                                <option value="nw">Nidwalden</option>
                                <option value="ow">Obwalden</option>
                                <option value="sh">Schaffhausen</option>
                                <option value="sz">Schwyz</option>
                                <option value="so">Solothurn</option>
                                <option value="sg">St. Gallen</option>
                                <option value="ti">Tessin</option>
                                <option value="tg">Thurgau</option>
                                <option value="ur">Uri</option>
                                <option value="vd">Waadt</option>
                                <option value="vs">Wallis</option>
                                <option value="zg">Zug</option>
                                <option value="zh">Zürich</option>
                            </select>
						</div><!--/col-md-3-->
                        <div class="col-md-3">
                        	<h2>Institution</h2>
                            <select name="institution" class="auswahl" id="institution_filter">
                                <option value="" selected>- Alle -</option>
                                <option value="schule">Schule</option>
                                <option value="klinik">Klinik</option>
                                <option value="praxis">Freie Praxis</option>
                                <option value="dienst">Logopäd. Dienst</option>
                                <option value="sonderschule">Sonderschule</option>
                                <option value="spd">SPD</option>
                                <option value="zentrum">Kompetenzzentrum</option>
                            </select>
                        </div><!--/col-md-3-->
                    </div><!--/row-->
                    <div class="col-md-6"><!--row-->
                    	<p id="output"></p>
                    <div><!--/row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->
        <script>
		// Initial alle Jobs laden beim Seitenaufruf
		$(document).ready(function() {
			// Alle Jobs initial laden
			loadJobs('', '');
			
			// Change-Event für Filter
			$(".auswahl").on("change", function() {
				var kanton = $('#kanton_filter').val();
				var institution = $('#institution_filter').val();
				loadJobs(kanton, institution);
			});
		});
		
		// Funktion zum Laden der Jobs via AJAX
		function loadJobs(kanton, institution) {
			$.ajax({
				url: "search_job.php",
				type: "POST",
				data: { 
					kanton: kanton, 
					institution: institution
				},
				dataType: "text",
				success: function(get_data) {
					$("#output").empty();
					$("#output").html(get_data);
					console.log("Jobs geladen - Kanton: " + kanton + ", Institution: " + institution);
				},
				error: function(xhr, status, error) {
					console.error("AJAX Error:", error);
					console.log("Response:", xhr.responseText);
				}
			});
		}
    </script>
    </body>
</html>