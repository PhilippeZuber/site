<?php
session_start();
if (isset($_SESSION['id'])) {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'addin';
$page_title     = 'Wortlab Add-in für Microsoft Word';
$page_meta_desc = 'Das Wortlab-Add-in für Microsoft Word: Wörter direkt im Dokument suchen, filtern und einfügen – ohne Browserwechsel. Für Logopädinnen und Lehrpersonen.';
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
                <div class="container-fluid">

                    <div class="row form-group">
                        <div class="col-md-8">
                            <img src="img/wortlab_logo.svg" alt="Logo Wortlab">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="page-header">
                                <h1>Wortlab für Word <small>– Das Office-Add-in für Logopädinnen</small></h1>
                            </div>

                            <p class="lead">Wortlab direkt in Microsoft Word nutzen: Wörter suchen, filtern und mit einem Klick ins Dokument einfügen – ohne den Browser öffnen zu müssen.</p>

                            <div class="alert alert-success">
                                <strong><span class="glyphicon glyphicon-ok-sign"></span> In Vorbereitung für Microsoft AppSource</strong> – Das Add-in wird in Kürze im Microsoft AppSource-Katalog verfügbar sein.
                            </div>
                            <img src="img/addin_screenshot.png" alt="Screenshot Wortlab Add-in" class="img-responsive" style="margin: 20px 0; border: 1px solid #ddd;">
                            <h2>Was kann das Add-in?</h2>
                            <p>Mit dem Wortlab-Add-in für Microsoft Word arbeiten Sie mit der gesamten Wortlab-Datenbank direkt in Ihrem Textdokument:</p>
                            <ul>
                                <li>Wörter nach Anfangsbuchstabe, Thema, Wortart und Alter suchen</li>
                                <li>Einzelne Wörter mit einem Klick in das Word-Dokument einfügen</li>
                                <li>Bilder zum Wort direkt ins Dokument einfügen</li>
                                <li>Persönliche Wortsammlungen laden und direkt verwenden</li>
                                <li>Sternchen-Wildcards in der Suche (*at, S*e usw.)</li>
                            </ul>

                            <h2>Voraussetzungen</h2>
                            <ul>
                                <li>Microsoft Word 2016 oder neuer <strong>oder</strong> Microsoft 365 (Word)</li>
                                <li>Windows oder macOS</li>
                                <li>Internetzugang während der Nutzung</li>
                                <li>Ein Wortlab-Benutzerkonto</li>
                            </ul>

                            <h2>Installation</h2>
                            <p>Die Installation ist unter Windows stark vereinfacht: Laden Sie das Installationsskript herunter und führen Sie es mit Rechtsklick aus (muss als Administrator ausgeführt werden).</p>

                            <div class="alert alert-success">
                                <strong><span class="glyphicon glyphicon-ok-sign"></span> Empfohlen für Windows:</strong>
                                Download + Rechtsklick auf die Datei + <em>Öffnen</em>. Das Skript lädt das Manifest automatisch herunter, bereitet den freigegebenen Add-in-Ordner vor und kopiert den UNC-Pfad in die Zwischenablage.
                            </div>

                            <ol>
                                <li>Installationsskript herunterladen und als Administrator ausführen (siehe Download-Box unten).</li>
                                <li>Kontollieren, ob C:\OfficeAddins existiert und freigegeben ist.</li>
                                <li>In Word einmalig den freigegebenen Ordner als <em>Vertrauenswürdigen Add-In-Katalog</em> eintragen (Netzwerkpfad verwenden).</li>
                                <div class="alert alert-info">
                                    <strong><span class="glyphicon glyphicon-ok-sign"></span> Nicht vergessen:</strong><br>
                                     <img src="img/wortlab_addin_im_menue_anzeigen.png" alt="Installation_in_Word"> Diesen Haken <em>setzen</em>. Damit das Add-in angezeigt wird.
                                </div>
                                <li>Word neu starten und das Add-in unter <em>Start</em> → <em>Add-Ins</em> → <em>Erweitert</em> öffnen.</li>
                            </ol>


                            <p><strong>macOS oder manuelle Installation:</strong> Nutzen Sie weiterhin die Manifest-Datei und den bisherigen Weg über den freigegebenen Ordner.</p>

                            <h2>Anmeldung im Add-in</h2>
                            <p>Nach der Installation melden Sie sich direkt im Add-in an:</p>
                            <ol>
                                <li>Öffnen Sie das Add-in in Word (Start → Add-Ins → Erweitert).</li>
                                <li>Geben Sie Ihre E-Mail und Ihr Passwort ein.</li>
                                <li>Klicken Sie <em>Anmelden</em>.</li>
                                <li>Geschafft! Sie können sofort Wörter suchen und einfügen.</li>
                            </ol>

                            <div class="panel panel-primary" style="margin-top: 30px;">
                                <div class="panel-heading">
                                    <h3 class="panel-title"><span class="glyphicon glyphicon-download-alt"></span> Manifest-Datei herunterladen</h3>
                                </div>
                                <div class="panel-body">
                                    <p>Laden Sie das Windows-Installationsskript (empfohlen) oder alternativ die Manifest-Datei herunter:</p>
                                    <p>
                                        <a href="downloads/install-wortlab-addin.cmd" class="btn btn-success btn-lg" download="install-wortlab-addin.cmd">
                                            <span class="glyphicon glyphicon-flash"></span> Windows-Installer herunterladen (.cmd)
                                        </a>
                                    </p>
                                    <a href="downloads/wortlab-addin-manifest.xml" class="btn btn-primary btn-lg" download="wortlab-addin-manifest.xml">
                                        <span class="glyphicon glyphicon-download-alt"></span> wortlab-addin-manifest.xml herunterladen
                                    </a>
                                    <p class="text-muted" style="margin-top: 10px;"><small>Installer: Windows CMD-Datei · Manifest: XML-Datei, ca. 5 KB · Version 0.1.0.0</small></p>
                                </div>
                            </div>

                            <h2>Support &amp; Feedback</h2>
                            <p>Haben Sie Fragen zum Add-in oder möchten Sie Feedback geben? Kontaktieren Sie uns gerne:</p>
                            <p>
                                <a href="mailto:info@wortlab.ch" class="btn btn-default">
                                    <span class="glyphicon glyphicon-envelope"></span> info@wortlab.ch
                                </a>
                            </p>

                            <p style="margin-top: 30px;"><a href="about.php">← Zurück zu «Über Wortlab»</a></p>

                        </div><!-- /col-md-8 -->

                        <div class="col-md-4">
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Auf einen Blick</strong></div>
                                <div class="panel-body">
                                    <dl class="dl-horizontal">
                                        <dt>Status</dt>
                                        <dd><span class="label label-success">AppSource (Beta)</span></dd>
                                        <dt>Version</dt>
                                        <dd>0.1.0</dd>
                                        <dt>Plattform</dt>
                                        <dd>Microsoft Word 2016+</dd>
                                        <dt>Systeme</dt>
                                        <dd>Windows, macOS</dd>
                                        <dt>Sprache</dt>
                                        <dd>Deutsch (Schweiz)</dd>
                                    </dl>
                                </div>
                            </div>
                            <div class="panel panel-default">
                                <div class="panel-heading"><strong>Schnelleinstieg</strong></div>
                                <div class="panel-body">
                                    <ol style="padding-left: 18px;">
                                        <li>Manifest herunterladen</li>
                                        <li>In freigegebenen Ordner legen</li>
                                        <li>In Word als Add-in-Katalog eintragen</li>
                                        <li>Wortlab-Konto verbinden</li>
                                        <li>Wörter suchen &amp; einfügen</li>
                                    </ol>
                                </div>
                            </div>
                        </div><!-- /col-md-4 -->

                    </div><!--/row-->

                </div><!--/container-->

                <?php include './footer.php'; ?>
            </div>
        </div>
    </body>
</html>
