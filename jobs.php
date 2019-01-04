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
<!doctype html>
<html>
    <?php include './header.php'; ?>
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                	<div class="row">
                    	<h1>Stellenangebote für Logopädinnen/Logopäden</h1>
                    </div>
                    <div class="row">
						<div class="col-md-3">
                            <h2>Kanton</h2>
                            <form class="form-inline" method="get" action="jobs.php">
                                <select name="kanton" class="auswahl" id="kanton">
                                    <option value="" selected>- Alle -</option>
                                    <option value="ag">Aargau</option>
                                    <option value="ar">Appenzell Ausserrhoden</option>
                                    <option value="bl">Appenzell Innerrhoden</option>
                                    <option value="bs">Basel-Landschaft</option>
                                    <option value="be">Basel-Stadt</option>
                                    <option value="be">Bern</option>
                                    <option value="be">Freiburg</option>
                                    <option value="be">Genf</option>
                                    <option value="be">Glarus</option>
                                    <option value="be">Graubünden</option>
                                    <option value="be">Jura</option>
                                    <option value="be">Luzern</option>
                                    <option value="be">Neuenburg</option>
                                    <option value="be">Nidwalden</option>
                                    <option value="be">Obwalden</option>
                                    <option value="be">Schaffhausen</option>
                                    <option value="be">Schwyz</option>
                                    <option value="be">Solothurn</option>
                                    <option value="be">St. Gallen</option>
                                    <option value="be">Tessin</option>
                                    <option value="be">Thurgau</option>
                                    <option value="be">Uri</option>
                                    <option value="be">Waadt</option>
                                    <option value="be">Wallis</option>
                                    <option value="be">Zug</option>
                                    <option value="be">Zürich</option>
                                </select>
						</div><!--/col-md-3-->
                        <div class="col-md-3">
                        	<h2>Institution</h2>
                                <select name="kanton" class="auswahl" id="kanton">
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
        <!-- jQuery -->
    </body>
</html>