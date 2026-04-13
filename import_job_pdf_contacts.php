<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
    exit();
} else {
    $user_id = $_SESSION['id'];
}

if ($_SESSION['role'] != 1) {
    header("Location:index.php");
    exit();
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'import_job_pdf_contacts';
$default_source_url = 'https://www.logopaedie.ch/stellenagebote-stellengesuche';
$source_url = $default_source_url;
$import_result = null;

ensure_job_pdf_contacts_table();

if (isset($_POST['run_import'])) {
    $source_url = filter_data($_POST['source_url']);

    if (!preg_match('/^https?:\/\//i', $source_url)) {
        $_SESSION['error'] = 'Bitte eine gueltige URL mit http:// oder https:// eingeben.';
        header("Location:import_job_pdf_contacts.php");
        exit();
    }

    $import_result = import_pdf_contacts_from_page($source_url, $user_id);
}

$contacts = array();
$contacts_result = get_result("SELECT id, email, source_pdf_url, status, first_seen_at, last_seen_at FROM job_pdf_contacts ORDER BY last_seen_at DESC LIMIT 200");
if ($contacts_result) {
    while ($row = mysqli_fetch_assoc($contacts_result)) {
        $contacts[] = $row;
    }
}
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
                            <h1>PDF-Kontakte importieren</h1>
                            <p class="text-muted">Manueller Import von E-Mail-Adressen aus verlinkten PDF-Stellenanzeigen.</p>

                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <strong>Fehler:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Import starten</h3>
                                </div>
                                <div class="panel-body">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="source_url">Quellseite mit PDF-Links</label>
                                            <input type="url" class="form-control" id="source_url" name="source_url" value="<?php echo htmlspecialchars($source_url); ?>" required>
                                        </div>
                                        <button type="submit" name="run_import" class="btn btn-primary">
                                            <span class="glyphicon glyphicon-play"></span> Import jetzt ausfuehren
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if (is_array($import_result)): ?>
                                <div class="panel panel-info">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Import-Ergebnis</h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-6 col-md-3"><strong>PDF-Links gefunden:</strong> <?php echo intval($import_result['pdf_links_found']); ?></div>
                                            <div class="col-sm-6 col-md-3"><strong>PDFs verarbeitet:</strong> <?php echo intval($import_result['pdfs_processed']); ?></div>
                                            <div class="col-sm-6 col-md-3"><strong>Neue E-Mails:</strong> <?php echo intval($import_result['inserted']); ?></div>
                                            <div class="col-sm-6 col-md-3"><strong>Duplikate:</strong> <?php echo intval($import_result['duplicates']); ?></div>
                                        </div>
                                        <hr>
                                        <p><strong>E-Mails erkannt (gesamt):</strong> <?php echo intval($import_result['emails_found']); ?></p>
                                        <p><strong>PDFs fehlgeschlagen:</strong> <?php echo intval($import_result['pdfs_failed']); ?></p>

                                        <?php if (!empty($import_result['errors'])): ?>
                                            <div class="alert alert-warning" role="alert">
                                                <strong>Hinweise:</strong>
                                                <ul style="margin-top: 8px; margin-bottom: 0;">
                                                    <?php foreach ($import_result['errors'] as $error): ?>
                                                        <li><?php echo htmlspecialchars($error); ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($import_result['failed_pdf_urls'])): ?>
                                            <p><strong>Nicht verarbeitbare PDF-Links (max. 20):</strong></p>
                                            <ul>
                                                <?php foreach (array_slice($import_result['failed_pdf_urls'], 0, 20) as $failed_pdf): ?>
                                                    <li><a href="<?php echo htmlspecialchars($failed_pdf); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($failed_pdf); ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Letzte importierte Kontakte (max. 200)</h3>
                                </div>
                                <div class="panel-body table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>E-Mail</th>
                                                <th>Quelle (PDF)</th>
                                                <th>Status</th>
                                                <th>Erstmals</th>
                                                <th>Zuletzt gesehen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($contacts)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-muted">Noch keine Kontakte importiert.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($contacts as $contact): ?>
                                                    <tr>
                                                        <td><?php echo intval($contact['id']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                                        <td>
                                                            <a href="<?php echo htmlspecialchars($contact['source_pdf_url']); ?>" target="_blank" rel="noopener">
                                                                <?php echo htmlspecialchars($contact['source_pdf_url']); ?>
                                                            </a>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($contact['status']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['first_seen_at']); ?></td>
                                                        <td><?php echo htmlspecialchars($contact['last_seen_at']); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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
