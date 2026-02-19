<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
    exit();
} else {
    $user_id = $_SESSION['id'];
}

// Nur Admin
if ($_SESSION['role'] != 1) {
    header("Location:index.php");
    exit();
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'approve_jobs';

// Alle pending Jobs abrufen
$sql = "SELECT j.id, j.name, j.institution, j.kanton, j.created_at, j.approval_token, u.firstname, u.lastname, u.email 
        FROM jobs j 
        LEFT JOIN user u ON j.creator_id = u.user_id 
        WHERE j.status = 'pending' 
        ORDER BY j.created_at DESC";
$result = get_result($sql);
$pending_jobs = array();
while ($row = mysqli_fetch_assoc($result)) {
    $pending_jobs[] = $row;
}

$pending_count = count($pending_jobs);

$kantone = array(
    'ag' => 'Aargau', 'ar' => 'Appenzell Ausserrhoden', 'ai' => 'Appenzell Innerrhoden',
    'bl' => 'Basel-Landschaft', 'bs' => 'Basel-Stadt', 'be' => 'Bern', 'fr' => 'Freiburg',
    'ge' => 'Genf', 'gl' => 'Glarus', 'gr' => 'Graubünden', 'ju' => 'Jura', 'lu' => 'Luzern',
    'ne' => 'Neuenburg', 'nw' => 'Nidwalden', 'ow' => 'Obwalden', 'sh' => 'Schaffhausen',
    'sz' => 'Schwyz', 'so' => 'Solothurn', 'sg' => 'St. Gallen', 'ti' => 'Tessin',
    'tg' => 'Thurgau', 'ur' => 'Uri', 'vd' => 'Waadt', 'vs' => 'Wallis', 'zg' => 'Zug', 'zh' => 'Zürich'
);
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
                            <h1>Stellenanzeigen freigeben</h1>
                            
                            <?php if(isset($_SESSION['success'])): ?>
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <strong>Erfolg!</strong> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if(isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <strong>Fehler:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title">Anstehende Genehmigungen: <span class="badge"><?php echo $pending_count; ?></span></h3>
                                </div>
                                <div class="panel-body">
                                    <?php if($pending_count === 0): ?>
                                        <p class="text-muted">Keine ausstehenden Stellenanzeigen.</p>
                                    <?php else: ?>
                                        <?php foreach($pending_jobs as $job): ?>
                                            <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h4 style="margin: 0 0 10px 0;"><?php echo htmlspecialchars($job['name']); ?></h4>
                                                        <p style="margin: 3px 0;"><strong>Institution:</strong> <?php echo htmlspecialchars($job['institution']); ?></p>
                                                        <p style="margin: 3px 0;"><strong>Kanton:</strong> <?php echo htmlspecialchars(isset($kantone[$job['kanton']]) ? $kantone[$job['kanton']] : $job['kanton']); ?></p>
                                                        <p style="margin: 3px 0;"><strong>Einsender:</strong> <?php echo htmlspecialchars($job['firstname'] . ' ' . $job['lastname']); ?> (<?php echo htmlspecialchars($job['email']); ?>)</p>
                                                        <p style="margin: 3px 0; color: #999; font-size: 12px;"><strong>Eingereicht:</strong> <?php echo date('d.m.Y H:i', strtotime($job['created_at'])); ?></p>
                                                    </div>
                                                    <div class="col-md-4" style="text-align: right;">
                                                        <a href="approve_job.php?job_id=<?php echo $job['id']; ?>&token=<?php echo $job['approval_token']; ?>&action=approve" 
                                                           class="btn btn-success btn-sm" onclick="return confirm('Stelle genehmigen und User benachrichtigen?');">
                                                            <span class="glyphicon glyphicon-ok"></span> Genehmigen
                                                        </a>
                                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal" 
                                                                onclick="setRejectJobId(<?php echo $job['id']; ?>, '<?php echo htmlspecialchars($job['approval_token']); ?>')">
                                                            <span class="glyphicon glyphicon-remove"></span> Ablehnen
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Stelle ablehnen</h4>
                    </div>
                    <form action="" method="GET">
                        <input type="hidden" id="rejectJobId" name="job_id" value="">
                        <input type="hidden" id="rejectToken" name="token" value="">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="rejectReason">Grund für Ablehnung:</label>
                                <textarea id="rejectReason" name="reason" class="form-control" rows="4" placeholder="z.B. Ungeeignete Inhalte, Spam, etc." required></textarea>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Stelle ablehnen und User benachrichtigen?');">
                                <span class="glyphicon glyphicon-remove"></span> Ablehnen & E-Mail senden
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include './footer.php'; ?>
        
        <script>
        function setRejectJobId(jobId, token) {
            document.getElementById('rejectJobId').value = jobId;
            document.getElementById('rejectToken').value = token;
        }
        </script>
    </body>
</html>
