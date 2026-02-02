<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'jobs';

// Stellenanzeige aufgeben
if (isset($_POST['job-submit'])) {
    $name = filter_data($_POST['name']);
    $kanton = filter_data($_POST['kanton']);
    $institution = filter_data($_POST['institution']);
    $stellenantritt = filter_data($_POST['stellenantritt']);
    $erscheinen_am = filter_data($_POST['erscheinen_am']);
    $pdf_url = filter_data($_POST['pdf_url']);
    
    // PDF-URL Validierung - nur .pdf erlauben
    if (!empty($pdf_url)) {
        // Prüfe ob URL mit https:// oder http:// beginnt
        if (strpos($pdf_url, 'http://') !== 0 && strpos($pdf_url, 'https://') !== 0) {
            $_SESSION['error'] = "Die URL muss mit http:// oder https:// beginnen.";
            header("Location:jobs.php");
            exit();
        }
        
        // Prüfe ob URL mit .pdf endet (case-insensitive)
        if (strtolower(substr($pdf_url, -4)) !== '.pdf') {
            $_SESSION['error'] = "Die URL muss auf .pdf enden.";
            header("Location:jobs.php");
            exit();
        }
    }
    
    // Daten für die jobs Tabelle vorbereiten
    $job_data = array(
        'name' => $name,
        'kanton' => $kanton,
        'institution' => $institution,
        'stellenantritt' => $stellenantritt,
        'erscheinen_am' => $erscheinen_am,
        'pdf_url' => $pdf_url
    );
    
    // Stellenanzeige in Datenbank speichern
    add_record('jobs', $job_data);
    
    // Redirect um Formular neu zu laden
    header("Location:jobs.php?success=1");
    exit();
}

// User-Daten für Modal-Vorausfüllung laden
$user = mysqli_fetch_assoc(get_user($user_id));

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
                        	<a href="#" data-toggle="modal" data-target="#JobsModal" class="btn btn-primary">
                                <span class="glyphicon glyphicon-plus"></span> Neue Stellenanzeige aufgeben
                            </a>
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