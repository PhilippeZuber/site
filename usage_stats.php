<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location:search.php");
    exit();
}

$user_id = $_SESSION['id'];

require_once('system/data.php');

$page = 'usage_stats';

$summary_today = get_login_stats_summary(1);
$summary_7 = get_login_stats_summary(7);
$summary_30 = get_login_stats_summary(30);
$daily_logins = get_login_stats_by_day(30);
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
                            <div class="panel panel-inverse">
                                <div class="panel-heading">
                                    <h4 class="panel-title">Nutzungsstatistik</h4>
                                </div>
                                <div class="panel-body">
                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Heute</h4>
                                                <p><strong>Logins:</strong> <?php echo (int) $summary_today['login_count']; ?></p>
                                                <p><strong>Aktive Nutzer:</strong> <?php echo (int) $summary_today['unique_users']; ?></p>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Letzte 7 Tage</h4>
                                                <p><strong>Logins:</strong> <?php echo (int) $summary_7['login_count']; ?></p>
                                                <p><strong>Aktive Nutzer:</strong> <?php echo (int) $summary_7['unique_users']; ?></p>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Letzte 30 Tage</h4>
                                                <p><strong>Logins:</strong> <?php echo (int) $summary_30['login_count']; ?></p>
                                                <p><strong>Aktive Nutzer:</strong> <?php echo (int) $summary_30['unique_users']; ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <h4>Logins pro Tag (30 Tage)</h4>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Datum</th>
                                                    <th>Logins</th>
                                                    <th>Aktive Nutzer</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (!empty($daily_logins)) { ?>
                                                <?php foreach ($daily_logins as $entry) { ?>
                                                    <tr>
                                                        <td><?php echo date('d.m.Y', strtotime($entry['log_date'])); ?></td>
                                                        <td><?php echo (int) $entry['login_count']; ?></td>
                                                        <td><?php echo (int) $entry['unique_users']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="3">Noch keine Login-Daten vorhanden.</td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
