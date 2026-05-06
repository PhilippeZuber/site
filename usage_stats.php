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

$selected_days = isset($_GET['days']) ? (int) $_GET['days'] : 30;
$allowed_days = array(7, 30, 90, 365);
if (!in_array($selected_days, $allowed_days)) {
    $selected_days = 30;
}

$top_limit = isset($_GET['top']) ? (int) $_GET['top'] : 10;
if ($top_limit < 5 || $top_limit > 50) {
    $top_limit = 10;
}

$summary_today = get_login_stats_summary(1);
$summary_7 = get_login_stats_summary(7);
$summary_30 = get_login_stats_summary(30);
$daily_logins = get_login_stats_by_day($selected_days);
$user_distribution = get_user_login_distribution($selected_days);
$top_users = get_top_users_by_logins($selected_days, $top_limit);
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
                                    <form class="form-inline" method="get" action="usage_stats.php" style="margin-bottom: 20px;">
                                        <div class="form-group" style="margin-right: 10px;">
                                            <label for="days">Zeitraum:&nbsp;</label>
                                            <select class="form-control" id="days" name="days">
                                                <option value="7" <?php echo $selected_days == 7 ? 'selected' : ''; ?>>7 Tage</option>
                                                <option value="30" <?php echo $selected_days == 30 ? 'selected' : ''; ?>>30 Tage</option>
                                                <option value="90" <?php echo $selected_days == 90 ? 'selected' : ''; ?>>90 Tage</option>
                                                <option value="365" <?php echo $selected_days == 365 ? 'selected' : ''; ?>>365 Tage</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-right: 10px;">
                                            <label for="top">Top-Liste:&nbsp;</label>
                                            <select class="form-control" id="top" name="top">
                                                <option value="5" <?php echo $top_limit == 5 ? 'selected' : ''; ?>>Top 5</option>
                                                <option value="10" <?php echo $top_limit == 10 ? 'selected' : ''; ?>>Top 10</option>
                                                <option value="20" <?php echo $top_limit == 20 ? 'selected' : ''; ?>>Top 20</option>
                                                <option value="50" <?php echo $top_limit == 50 ? 'selected' : ''; ?>>Top 50</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Anzeigen</button>
                                    </form>

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

                                    <div class="row" style="margin-bottom: 20px;">
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Nutzer (<?php echo (int) $selected_days; ?> Tage)</h4>
                                                <p><strong>Unterschiedliche Nutzer:</strong> <?php echo (int) $user_distribution['total_users']; ?></p>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Davon wiederkehrend</h4>
                                                <p><strong>> 1 Login:</strong> <?php echo (int) $user_distribution['repeat_users']; ?></p>
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <div class="well text-center">
                                                <h4>Davon einmalig</h4>
                                                <p><strong>= 1 Login:</strong> <?php echo (int) $user_distribution['one_time_users']; ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <h4>Top-Nutzer (<?php echo (int) $selected_days; ?> Tage)</h4>
                                    <div class="table-responsive" style="margin-bottom: 25px;">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Rang</th>
                                                    <th>Name</th>
                                                    <th>E-Mail</th>
                                                    <th>Rolle/Beruf</th>
                                                    <th>Logins</th>
                                                    <th>Letzter Login</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if (!empty($top_users)) { ?>
                                                <?php $rank = 1; ?>
                                                <?php foreach ($top_users as $entry) { ?>
                                                    <tr>
                                                        <td><?php echo $rank; ?></td>
                                                        <td><?php echo htmlspecialchars(trim($entry['firstname'] . ' ' . $entry['lastname'])); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($entry['job']); ?></td>
                                                        <td><?php echo (int) $entry['login_count']; ?></td>
                                                        <td><?php echo date('d.m.Y H:i', strtotime($entry['last_login_at'])); ?></td>
                                                    </tr>
                                                    <?php $rank++; ?>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="6">Keine Logins im gewählten Zeitraum gefunden.</td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <h4>Logins pro Tag (<?php echo (int) $selected_days; ?> Tage)</h4>
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
