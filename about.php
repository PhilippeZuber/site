<?php
session_start();
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'about';
?>
<!DOCTYPE html>
<html lang="de">
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
                            <div class="alert alert-success"><span class="glyphicon glyphicon-glass" aria-hidden="true"></span> Wortlab steht nun allen offen! Bitte erzähle das weiter. ;-)</div>
                            <div class="alert alert-success"><span class="glyphicon glyphicon-gift" aria-hidden="true"></span> In den letzten Monaten sind neue Funktionen (z.B. Memory) dazugekommen.</div>
                        </div>
                    </div><!--/row-->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="page-header">
                                <h1>Wörter, die mit einem Buchstaben beginnen – Wortlisten für Logopädinnen</h1>
                            </div>
                                <p class="lead">Sie suchen Wortlisten wie «Wörter, die mit S beginnen» oder «Wörter mit K am Anfang» für Therapie, Förderung oder Unterricht? <strong>Wortlab</strong> ist eine spezialisierte Wort-Datenbank, mit der Logopädinnen, Lehrpersonen und Therapeutinnen gezielt passende Wörter finden.</p>
                                <p>Wortlab ist ein Projekt von Philippe Zuber mit der Unterstützung von Jacqueline Zuber. Mit Wortlab führen wir unsere Berufe – Multimediaproducer und Logopädin – zusammen, um den Arbeitsalltag in Logopädie und Schule zu erleichtern und möglichst vielen Kindern eine wirksame Sprachförderung zu ermöglichen.</p>
                                <h2>Was ist Wortlab?</h2>
                                <p>Wortlab ist eine webbasierte Anwendung, mit der Sie Wörter nach unterschiedlichen Kriterien filtern können: Anfangsbuchstabe, Lauttreue, Wortlänge, Wortart, Thema und Altersstufe. So erstellen Sie in wenigen Sekunden Wortlisten für Ihre spezifische Fragestellung, ohne lange in Büchern oder im Internet zu suchen.</p>
                                <h2>Wörter nach Buchstaben, Lauten und Themen suchen</h2>
                                <p>Mit Wortlab können Sie zum Beispiel alle Wörter anzeigen lassen, die mit einem bestimmten Buchstaben oder Anlaut beginnen oder enden. Zusätzlich filtern Sie nach Themen wie Tiere, Haushalt, Schule, Gefühle oder Berufe sowie nach Altersbereichen wie Kindergarten, Unterstufe oder Mittelstufe.</p>
                                <p>Auf Wunsch werden nur Wörter mit Bild angezeigt – ideal für nicht-lesende Kinder, Kinder mit Sprachentwicklungsstörungen oder DaZ-Lernende.</p>
                                <h2>Wie unterstützt Wortlab Therapie und Unterrichtsvorbereitung?</h2>
                                <p>Wortlab hilft Ihnen bei der Planung von Therapiesitzungen, Förderlektionen und Unterrichtseinheiten, indem es passende Wortlisten für Ihre Ziele bereitstellt. Sie können damit Übungen zu Lautanbahnung, Minimalpaaren, Wortschatzaufbau oder thematischen Wortfeldern schneller vorbereiten.</p>
                                <p>Dank der Filterfunktionen sparen Sie Zeit bei der Materialsuche und können sich stärker auf die inhaltliche Arbeit mit den Kindern konzentrieren.</p>
                                <p>Mit der Memory-Funktion können Sie spielerisch das Gelernte vertiefen. Dank Auswahl der gewünschten Wörter ist das Memory individuell auf das Kind angepasst.</p>
                                <h2>Wortlab ausprobieren</h2>
                                <p>Wortlab befindet sich im laufenden Ausbau und wird kontinuierlich um neue Wörter und Funktionen ergänzt. Probieren Sie die Suche jetzt aus und finden Sie in wenigen Klicks passende Wortlisten für Ihre logopädische oder pädagogische Arbeit.</p>
                                <p><a href="search.php" class="btn btn-primary">Wörter nach Anfangsbuchstabe und Thema suchen</a></p>
                            <div>
                                <img src="img/kinder.svg" class="img-responsive" alt="Kinder">
                            </div>
                        </div>
                    </div><!--/row-->
                </div><!--/container-->     
            </div><!--/content-->
        </div><!--/wrapper-->
        <?php include 'footer.php'; ?>
    </body>
</html>