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

?>
<!DOCTYPE html>
<html lang="de">
    <?php include './header.php'; ?>
    <!--Modal window for jobs-->
    <div class="modal fade" id="JobsModal" tabindex="-1" role="dialog" aria-labelledby="jobs-wortlab">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                    <div class="modal-header">
                        <h4 class="modal-title" id="profil-wortlab">Anzeige aufgeben</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="Gender" class="col-sm-2 form-control-label">Anrede</label>
                            <div class="col-sm-5">
                                <select class="form-control form-control-sm" id="Gender" name="gender">
                                    <option <?php if ($user['gender'] == "") echo "selected"; ?> value="">--</option>
                                    <option <?php if ($user['gender'] == "Frau") echo "selected"; ?> value="Frau">Frau</option>
                                    <option <?php if ($user['gender'] == "Herr") echo "selected"; ?> value="Herr">Herr</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Vorname" class="col-sm-2 col-xs-12 form-control-label">Name</label>
                            <div class="col-sm-5 col-xs-6">
                                <input  type="text" class="form-control form-control-sm"
                                        id="Vorname" placeholder="Vorname"
                                        name="firstname" value="<?php echo $user['firstname']; ?>">
                            </div>
                            <div class="col-sm-5 col-xs-6">
                                <input  type="text" class="form-control form-control-sm"
                                        id="Nachname" placeholder="Nachname"
                                        name="lastname" value="<?php echo $user['lastname']; ?>">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Email" class="col-sm-2 form-control-label">E-Mail</label>
                            <div class="col-sm-10">
                                <input  type="email" class="form-control form-control-sm"
                                        id="Email" placeholder="E-Mail" required
                                        name="email" value="<?php echo $user['email']; ?>">
                            </div>
                        </div>
                        <hr />   
                        <div class="form-group row">
                            <label for="pdf" class="col-sm-2 form-control-label">PDF</label>
                            <div class="col-sm-10">
                                <input type="file" name="pdf_stelle">
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-5 col-sm-offset-2">
                                <label for="Institution" class="form-control-label">Institution</label>
                                <select class="form-control form-control-sm" id="Institution" name="institution">
                                    <option <?php if ($user['institution'] == "") echo "selected"; ?> value="">--</option>
                                    <option <?php if ($user['institution'] == "Schule") echo "selected"; ?> value="schule">Schule</option>
                                    <option <?php if ($user['institution'] == "Klinik") echo "selected"; ?> value="klinik">Klinik</option>
                                    <option <?php if ($user['institution'] == "Praxis") echo "selected"; ?> value="praxis">Freie Praxis</option>
                                    <option <?php if ($user['institution'] == "Dienst") echo "selected"; ?> value="dienst">Logopäd. Dienst</option>
                                    <option <?php if ($user['institution'] == "Sonderschule") echo "selected"; ?> value="sonderschule">Sonderschule</option>
                                    <option <?php if ($user['institution'] == "SPD") echo "selected"; ?> value="spd">SPD</option>
                                    <option <?php if ($user['institution'] == "Kompetenzzentrum") echo "selected"; ?> value="zentrum">Kompetenzzentrum</option>
                                </select>
                            </div>
                            <div class="col-sm-5">
                                <label for="Kanton" class="form-control-label">Kanton</label>
                                <select class="form-control form-control-sm" id="Kanton" name="kanton">
                                    <option <?php if ($user['kanton'] == "") echo "selected"; ?> value="">--</option>
                                    <option <?php if ($user['kanton'] == "Aargau") echo "selected"; ?> value="ag">Aargau</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ar">Appenzell Ausserrhoden</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ai">Appenzell Innerrhoden</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="bl">Basel-Landschaft</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="bs">Basel-Stadt</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="be">Bern</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="fr">Freiburg</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ge">Genf</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="gl">Glarus</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="gr">Graubünden</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ju">Jura</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="lu">Luzern</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ne">Neuenburg</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="nw">Nidwalden</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ow">Obwalden</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="sh">Schaffhausen</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="sz">Schwyz</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="so">Solothurn</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="sg">St. Gallen</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ti">Tessin</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="so">Solothurn</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="tg">Thurgau</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="ur">Uri</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="vd">Waadt</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="vs">Wallis</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="zg">Zug</option>
                                    <option <?php if ($user['kanton'] == "Appenzell Ausserrhoden") echo "selected"; ?> value="zh">Zürich</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Abbrechen</button>
                        <button type="submit" class="btn btn-success btn-sm" name="update-submit">Änderungen speichern</button>
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
                        </div>
                    </div>
                    <div class="row">
                    	<div class="col-md-12">
                        	<a href="#" data-toggle="modal" data-target="#JobsModal">
                                <p>Testlink Stellenanzeige aufgeben</p>
                            </a>
                        </div>
                    </div>
                    <div class="row">
						<div class="col-md-3">    
                            <h2>Kanton</h2>
                            <form class="form-inline" method="get" action="jobs.php">
                                <select name="kanton" class="auswahl" id="kanton">
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
                                <select name="institution" class="auswahl" id="institution">
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
                            </form>       
                    </div><!--/row-->
                    <div class="col-md-6"><!--row-->
                    	<p id="output"></p>
                    <div><!--/row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->
        <script>
		// ajax
	
		$(".auswahl").change(function(event) {         // Bei Klick auf den "posten"-Button
			event.preventDefault();                           // Absenden des Formulars unterbinden
			var kanton = $('#kanton option:selected').attr( "value");
			var institution = $('#institution option:selected').attr( "value");
	
			$.ajax({                    // Initialisierung eines AJAX-Requests
				url: "search_job.php",               // Die in Ajax ablaufenden Funktionen müssen zwingend in diesem File stattfinden.
				type: "POST",                           // Sendemethode der Daten POST
				data: { kanton: kanton, institution: institution}, // zu sendenden Daten; Die Attribute werden als "Variable" gesendet.
				dataType: "text",                 //Die Form der Daten. Kann z.B. auch HTML oder JSon sein.
		
				success:function( get_data ) {             // Bei erfolgreichem Request: Den zu empfangenden Daten einen "Namen" zuweisen.
					// console.log(get_data);
					html = $.parseHTML( get_data );                    // empfangenen Text als HTML parsen
					$("#output").empty();                           //Das Ausgabefeld mit der ID output wird geleert
					$(html).hide().prependTo("#output").show(500); // Das Ausgabefeld wird mit dem Inhalt gefüllt.
					console.log(get_data);	
				}
			});
		});
    </script>
    </body>
</html>