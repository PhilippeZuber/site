<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'about';
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
						<div class="col-md-8">
							<img src="img/wortlab_logo.svg" alt="Logo Wortlab" class="img-thumbnail">
							<p>Wortlab ist Teil der Bacheloarbeit von Philippe Zuber.</p><p>Wortlab befindet sich zurzeit in einer Testphase. D.h. die Datenbank von Wortlab ist noch unvollständig, weshalb einige Filterkriterien noch nicht funktionieren. Sollte sich herausstellen, dass eine Nachfrage an Wortlab besteht, wird die Entwicklung weitergeführt.</p>
                            <div class="alert alert-warning" role="alert">Bitte behalte deine Login Daten für dich. Aus Bildrechtlichen sowie Sicherheitsgründen, sollte der Zugang beschränkt bleiben. Ausserdem solltest du kein Passwort verwenden, dass du auch sonst irgendwo einsetzt.</div>
						</div>
                    </div><!--row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->

        <!-- jQuery -->
    </body>
</html>