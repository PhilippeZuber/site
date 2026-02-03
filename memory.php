<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
    exit;
}
$user_id = $_SESSION['id'];
require_once('system/data.php');
require_once('system/security.php');

$page = 'memory';

$ids_raw = isset($_GET['ids']) ? filter_data($_GET['ids']) : '';
$ids_raw = preg_replace('/[^0-9,]/', '', $ids_raw);
$mode = isset($_GET['mode']) ? filter_data($_GET['mode']) : 'image';
$pairs = isset($_GET['pairs']) ? filter_data($_GET['pairs']) : '';
$mode = in_array($mode, array('image', 'text', 'mixed')) ? $mode : 'image';
$pairs = preg_replace('/[^0-9]/', '', $pairs);
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
                            <h1>Memory</h1>
                            <?php if ($ids_raw == '') { ?>
                                <div class="alert alert-warning">Keine Wörter ausgewählt. Bitte gehe zurück zur Suche.</div>
                            <?php } else { ?>
                                <div class="alert alert-info" id="memory-info">
                                    Das Memory wird vorbereitet. Viel Spass beim Spielen!
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="row" style="margin-bottom: 15px;">
                        <div class="col-md-12">
                            <div class="memory-toolbar">
                                <span class="label label-primary">Züge: <span id="memory-moves">0</span></span>
                                <span class="label label-primary" style="margin-left: 10px;">Zeit: <span id="memory-time">0</span>s</span>
                                <button id="memory-restart" class="btn btn-default btn-sm" style="margin-left: 10px;">Neustart</button>
                                <a href="search.php" class="btn btn-link btn-sm">Zurück zur Suche</a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="memory-board" class="memory-board" data-ids="<?php echo htmlspecialchars($ids_raw); ?>" data-mode="<?php echo htmlspecialchars($mode); ?>" data-pairs="<?php echo htmlspecialchars($pairs); ?>"></div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-12">
                            <div id="memory-result" class="alert alert-success" style="display:none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include './footer.php'; ?>
        <script src="js/memory.js"></script>
        <script>
            $(document).ready(function() {
                $('#sidebar').addClass('active');
            });
        </script>
    </body>
</html>
