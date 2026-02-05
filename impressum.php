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
                    <div class="row">
                        <div class="col-md-8">
                            <div class="page-header">
                                <h1>Impressum</h1>
                            </div>
                                <h2>Kontakt</h2>
                                <a class="btn btn-default" href="mailto:info@wortlab.ch" role="button">info@wortlab.ch</a>
                                <h2>Datenschutzerklärung</h2>
                                <h3>1. Geltungsbereich</h3>
                                <p>Der Schutz Ihrer persönlichen Daten ist uns wichtig. Diese Datenschutzerklärung soll sowohl die Nutzer des Internet-Angebotes wortlab.ch, als auch Kunden, Lieferanten und andere Geschäftspartner über die Verarbeitung personenbezogener Daten
                                 in der EU und der Schweiz informieren. Daher müssen nicht alle Teile dieser Information auf Sie zutreffen. Personenbezogene Daten im Sinne dieser Datenschutzerklärung sind alle Daten, die auf Sie persönlich bezogen oder beziehbar sind, wie etwa Name, Adresse oder E-Mail-Adresse.</p>
                                <h3>2. Automatisierte Datenerhebung und -verarbeitung</h3>
                                <p><b>2.1 </b> Wie bei jeder Website erhebt dieser Server automatisch und temporär Angaben in den Server-Logfiles, die durch den Browser übermittelt werden, sofern dies nicht von Ihnen deaktiviert wurde. Wenn Sie die Website wortlab.ch betrachten möchten, erheben wir die folgenden Daten, die für uns technisch erforderlich sind, um Ihnen unsere Website anzuzeigen und Stabilität und Sicherheit zu gewährleisten:</p>
                                <ul>
                                    <li>IP-Adresse des anfragenden Rechners</li>
                                    <li>Dateianfrage des Clients</li>
                                    <li>Den http-Antwort-Code</li>
                                    <li>Die Internet-Seite, von der aus Sie uns besuchen (Referrer URL)</li>
                                    <li>Die Uhrzeit der Serveranfrage</li>
                                    <li>Browsertyp und -version</li>
                                    <li>Verwendetes Betriebssystem des anfragenden Rechners</li>
                                </ul>
                                <p><b>2.2 </b>Eine personenbezogene Auswertung dieser Server-Logfile-Daten findet nicht statt. Sofern vorbezeichnete Informationen personenbezogene Daten enthalten (insbesondere die IP-Adresse), erfolgt die Erhebung auf der Rechtsgrundlage von Art. 6 Abs. 1 lit. f DSGVO. Unser berechtigtes Interesse liegt dabei darin, das ordnungsgemässe Funktionieren unserer Website sicherzustellen. Sofern Sie weitere Informationen hinsichtlich der nach Art. 6 Abs. 1 lit. f DSGVO durchzuführenden Interessenabwägung benötigen, kontaktieren Sie uns bitte. Die Erhebung der vorstehend genannten Daten ist für die Bereitstellung der Funktionen unserer Website erforderlich.</p>
                                <p><b>2.3 </b>Diese Website nutzt keine Webanalyse-Tools (z. B. Google Analytics). Es findet keine Auswertung Ihres Nutzungsverhaltens zu Statistik- oder Marketingzwecken statt.</p>
                                <p><b>2.4 Grundlegende funktionale Cookies</b></p>
                                <p><b>2.4.1 </b>Neben den in Ziff. 2.2 genannten Cookies verwendet diese Website weitere Cookies. Bei Cookies handelt es sich um kleine Textdateien, die lokal im Zwischenspeicher Ihres Browsers gespeichert werden. Nachfolgend aufgezählte Cookies werden von uns nur verwendet, um die Durchführung bzw. Bereitstellung des von Ihnen genutzten Dienstes zu gewährleisten gem. Art. 6 Abs. 1 lit. f DSGVO. Unser berechtigtes Interesse an der Datenverarbeitung liegt dabei darin, die Website-Einstellungen für das von Ihnen verwendete Endgerät zu optimieren und die Benutzeroberflächen anzupassen. Sofern Sie weitere Informationen hinsichtlich der nach Art. 6 Abs. 1 lit. f DSGVO durchzuführenden Interessenabwägung benötigen, kontaktieren Sie uns bitte unter den oben angegebenen Kontaktdaten. Folgende Arten von Cookies, deren Umfang und Funktionsweise im Folgenden erläutert werden, werden auf dieser Website verwendet:</p>
                                <ul>
                                    <li>Transiente Cookies (dazu 2.4.2)</li>
                                    <li>Persistente Cookies (dazu 2.4.3)</li>
                                </ul>
                                <p><b>2.4.2 </b>Transiente Cookies werden automatisiert gelöscht, wenn Sie den Browser schliessen. Dazu zählt insbesondere der Session-Cookie. Dieser speichert eine sogenannte Session-ID, mit welcher sich verschiedene Anfragen Ihres Browsers der gemeinsamen Sitzung zuordnen lassen. Dadurch kann Ihr Rechner wiedererkannt werden, wenn Sie auf unsere Website zurückkehren. Der Session-Cookies wird gelöscht, wenn Sie sich ausloggen oder den Browser schliessen.</p>
                                <p><b>2.4.3 </b>Persistente Cookies werden automatisiert nach einer vorgegebenen Dauer gelöscht, die sich je nach Cookie unterscheiden kann. Sie können die Cookies in den Sicherheitseinstellungen Ihres Browsers jederzeit löschen. Im Fall von wortlab.ch wird ein einziger Cookie persistent gespeichert, der nach spätestens 360 Tagen automatisch gelöscht wird.</p>
                                <p><b>2.4.4 </b>Sie können Ihre Browser-Einstellung entsprechend Ihren Wünschen konfigurieren und z. B. die Annahme von Third-Party-Cookies oder von allen Cookies ablehnen. Wir weisen Sie darauf hin, dass Sie dann eventuell nicht alle Funktionen dieser Website nutzen können.</p>
                                <h3>3. Datenerhebung und -verarbeitung freiwillig mitgeteilter Daten</h3>
                                <p>Wir erheben und verarbeiten uns freiwillig mitgeteilte personenbezogene Daten bei der Interaktion (zum Beispiel über Email, Telefon oder das registrieren Formular der Webseite).</p>
                                <p>Die Datenverarbeitung erfolgt zu folgenden Zwecken:</p>
                                <p><b>3.1 </b>Zur Bereitstellung und Personalisierung der Dienstleistungen des online Portals wortlab.ch</p>
                                <p><b>3.2 </b>Wir verwenden Ihre Daten wie in dieser Datenschutzerklärung näher beschrieben ausserdem zu Werbezwecken, insbesondere in Form von E-Mail Newslettern oder Nutzerumfragen oder Kontaktaufnahme z.B. durch SMS oder per Telefon. Die Rechtsgrundlage hierfür sind unsere berechtigten Interessen nach Art. 6 Abs. 1 lit. f DSGVO an für Sie relevanten Werbemassnahmen. Sofern Sie weitere Informationen hinsichtlich der nach Art. 6 Abs. 1 lit. f DSGVO durchzuführenden Interessenabwägung benötigen, kontaktieren Sie uns bitte unter den oben angegebenen Kontaktdaten. Sie können der Nutzung Ihrer Daten für Werbezwecke jederzeit widersprechen indem Sie sich vom Newsletter abmelden. </p>
                                <p><b>3.3 </b>Sofern wir nach lokalen Gesetzen in einzelnen Ländern verpflichtet sind, für die genannten Werbemassnahmen Ihre vorherige Einwilligung einzuholen, werden wir dies selbstverständlich tun. Rechtsgrundlage für die Verarbeitung Ihrer Daten ist dann Ihre Einwilligung (Art. 6 Abs. 1 lit. a DSGVO). Ihre Einwilligung können Sie jederzeit widerrufen. Bitte wenden Sie sich hierfür an uns unter der oben genannten Kontaktmöglichkeit oder folgen Sie den jeweiligen Anweisungen in unseren Werbenachrichten. Durch den Widerruf der Einwilligung wird die Rechtmässigkeit der aufgrund der Einwilligung erfolgten Verarbeitung nicht berührt. Sie sind nicht verpflichtet uns Ihre Daten zu Werbezwecken zur Verfügung zu stellen. Ohne diese Daten sind wir jedoch nicht in der Lage, Ihnen Werbung zuzusenden.</p>
                                <h3>3.Weitergabe Ihrer Daten an Dritte</h3>
                                <p>Grundsätzlich erfolgt eine Weitergabe Ihrer personenbezogenen Daten ohne Ihre ausdrückliche vorherige Einwilligung nur aus gründen der Rechtsverfolgung.</p>
                                <p> Wenn es zur Aufklärung einer rechtswidrigen Nutzung unserer Dienste oder für die Rechtsverfolgung erforderlich ist, werden personenbezogene Daten an die Strafverfolgungsbehörden sowie gegebenenfalls an geschädigte Dritte weitergeleitet. Dies geschieht jedoch nur dann, wenn konkrete Anhaltspunkte für ein gesetzwidriges beziehungsweise missbräuchliches Verhalten vorliegen. Eine Weitergabe kann auch dann stattfinden, wenn dies der Durchsetzung von Nutzungsbedingungen oder anderer Vereinbarungen dient. Wir sind zudem gesetzlich verpflichtet, auf Anfrage bestimmten öffentlichen Stellen Auskunft zu erteilen. Dies sind Strafverfolgungsbehörden, Behörden, die bussgeldbewährte Ordnungswidrigkeiten verfolgen und die Finanzbehörden. Die Weitergabe dieser Daten erfolgt auf Grundlage unseres berechtigten Interesses an der Bekämpfung von Missbrauch, der Verfolgung von Straftaten und der Sicherung, Geltendmachung und Durchsetzung von Ansprüchen, Art. 6 Abs. 1 lit. f DSGVO. Sofern Sie weitere Informationen hinsichtlich der nach Art. 6 Abs. 1 lit. f DSGVO durchzuführenden Interessenabwägung benötigen, kontaktieren Sie uns bitte unter der oben angegebenen Kontaktmöglichkeit.</p>
                                <h3>4. Löschung Ihrer Daten</h3>
                                <p><b>4.1 </b>Mit dem Löschen Ihres Profils auf Wortlab werden sämtliche in den Profileinstellungen angegebenen Daten ausser der E-Mail Adresse dauerhaft gelöscht. Sie können Ihr Profil in den Profileinstellungen löschen oder indem Sie unter der oben angegebenen Kontaktmöglichkeit die Löschung beantragen.</p>
                                <p>Um zusätzlich die E-Mail Adresse zu löschen müssen Sie sich vom Newsletter abmelden. Sollten Sie beim Newsletter nicht angemeldet sein, genügt das Löschen des Profils.</p>                                
                                <h3>5. Ihre Rechte</h3>
                                <p>Ihnen stehen grundsätzlich die Rechte auf Auskunft, Berichtigung und Löschung Ihrer Personendaten zu. Wenn Sie glauben, dass die Verarbeitung Ihrer Daten gegen das Datenschutzrecht verstösst oder Ihre datenschutzrechtlichen Ansprüche sonst in einer Weise verletzt worden sind, können Sie sich bei der Aufsichtsbehörde beschweren. https://www.edoeb.admin.ch/edoeb/de/home.html</p>
                                <h3>5. Links zu anderen Webseiten</h3>
                                <p>Es ist möglich, dass wir Links zu anderen Webseiten anbieten. Diese Online-Datenschutzerklärung gilt nur für wortlab.ch und nicht für Webseiten von Dritten. Unsere Webseiten können Links zu anderen Webseiten enthalten, die für Sie von Interesse sein könnten. Aufgrund der Natur des Internets garantieren wir jedoch nicht für den Schutz Ihrer persönlichen Daten auf diesen Webseiten und übernehmen keinerlei Verantwortung für die Inhalte anderer Webseiten. Diese Online-Datenschutzerklärung gilt nicht für verlinkte Webseiten, die nicht von wortlab.ch stammen. Lassen Sie stets Vorsicht walten, wenn Sie einen Link zu einer anderen Webseite öffnen, und lesen Sie sich die Datenschutzerklärung der entsprechenden Seite durch.</p>
                                <h2>Rechtshinweise</h2>
                                <h3>Urheberrecht</h3>
                                <p>© Copyright wortlab.ch | Philippe Zuber. Alle Rechte vorbehalten. Sämtliche Texte, Bilder, Grafiken, Animationen, Videos, Sounds und sonstige Werke sowie deren Anordnung auf der Website unterliegen dem Schutz des Urheberrechts und anderer Schutzgesetze. Der Inhalt dieser Website darf ohne vorherige schriftliche Genehmigung von wortlab.ch | Philippe Zuber nicht zu kommerziellen Zwecken kopiert, verbreitet, verändert oder Dritten zugänglich gemacht werden. Wir weisen darauf hin, dass auf der Website enthaltene Bilder teilweise dem Urheberrecht Dritter unterliegen.</p>
                                <h3>Haftung</h3>
                                <p>Die auf unserer Website präsentierten Inhalte werden von wortlab.ch mit grösstmöglicher Sorgfalt erstellt und aktualisiert. Trotzdem kann keine Gewähr für die Richtigkeit, Aktualität und Vollständigkeit der zur Verfügung gestellten Inhalte übernommen werden. Jegliche Haftung für Schäden, die direkt oder indirekt aus der Nutzung dieser Website entstehen, wird, soweit gesetzlich zulässig, ausgeschlossen.</p>
                        </div>
                    </div><!--/row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->
        <?php include 'footer.php'; ?>
    </body>
</html>