<?php
session_start();
if (isset($_SESSION['id'])) {
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
                    <div class="row form-group">
						<div class="col-md-8">
							<img src="img/wortlab_logo.svg" alt="Logo Wortlab">
                        </div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-8">
							<img src="img/Philinne.jpg" class="img-circle img-responsive center-block" alt="Philippe & Jacqueline">
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning" role="alert"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Bitte behalte deine Login Daten für dich. Aus Bildrechtlichen Gründen, sollte der Zugang beschränkt bleiben.</div>
                            <div class="alert alert-success"><span class="glyphicon glyphicon-gift" aria-hidden="true"></span> In den letzten Monaten sind zahlreiche neue Bilder und Wörter dazugekommen.</div>
                        </div>
                    </div><!--/row-->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="page-header">
                                <h1>Philippe & Jacqueline Zuber</h1>
                            </div>
                                <p class="lead">Wortlab ist ein Projekt von Philippe Zuber mit der Unterstützung von Jacqueline Zuber. Mit Wortlab führen wir unsere Berufe – Multimediaproducer & Logopädin, zusammen. Unser Ziel ist es Logopädinnen und anderen Interessierten den Arbeitsalltag zu erleichtern und möglichst viele Kinder davon profitieren zu lassen.</p>
                                <p><strong>«Wörter die mit x anfangen»</strong> vielleicht haben Sie bereits genau diese Phrase in eine herkömmliche Suchmaschine eingegeben und wurden früher oder später fündig. Wortlab will das Suchen nach bestimmten Wörtern und Bildern vereinfachen. Um dies zu ermöglichen ist Wortlab auf die Mitarbeit vieler Benutzer angewiesen. Sie selbst können Wörter einsenden, die wiederum von Administratoren eingepflegt werden.</p>
                                <p>Wortlab befindet sich zurzeit in einer Testphase. D.h. die Datenbank von Wortlab ist noch unvollständig, weshalb einige Filterkriterien noch nicht funktionieren. Sollte sich herausstellen, dass eine Nachfrage an Wortlab besteht, wird die Entwicklung weitergeführt.</p>
                            <!--<div class="embed-responsive embed-responsive-16by9">
                                <iframe src="https://player.vimeo.com/video/282533727?title=0&byline=0&portrait=0 fullscreen" allowfullscreen></iframe>
                            </div>-->
                            <div>
                                <img src="img/kinder.svg" class="img-responsive" alt="Kinder">
                            </div>
                        </div>
                    </div><!--/row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->
    </body>
</html>