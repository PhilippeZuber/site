<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'subscription_management';

if (isset($_POST['subscription-update'])) {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $subscription_status = isset($_POST['subscription_status']) ? filter_data($_POST['subscription_status']) : 'free';
    $subscription_plan_code = isset($_POST['subscription_plan_code']) ? filter_data($_POST['subscription_plan_code']) : '';
    $subscription_expires_at_raw = isset($_POST['subscription_expires_at']) ? trim($_POST['subscription_expires_at']) : '';

    if (!in_array($subscription_status, array('free', 'active', 'expired'), true)) {
        $_SESSION['error'] = 'Ungültiger Abo-Status.';
        header('Location: subscription_management.php');
        exit();
    }

    if ($user_id <= 0) {
        $_SESSION['error'] = 'Ungültige Benutzer-ID.';
        header('Location: subscription_management.php');
        exit();
    }

    $subscription_expires_at_sql = 'NULL';
    if ($subscription_expires_at_raw !== '') {
        $timestamp = strtotime($subscription_expires_at_raw);
        if ($timestamp === false) {
            $_SESSION['error'] = 'Ungültiges Ablaufdatum.';
            header('Location: subscription_management.php');
            exit();
        }
        $subscription_expires_at_sql = "'" . date('Y-m-d H:i:s', $timestamp) . "'";
    }

    if ($subscription_status === 'active' && $subscription_plan_code === '') {
        $subscription_plan_code = 'yearly_manual';
    }

    $subscription_activated_sql = "subscription_activated_at = subscription_activated_at";
    if ($subscription_status === 'active') {
        $subscription_activated_sql = "subscription_activated_at = NOW()";
    }

    $sql = "UPDATE user SET "
        . "subscription_status = '" . $subscription_status . "', "
        . "subscription_plan_code = '" . $subscription_plan_code . "', "
        . "subscription_expires_at = " . $subscription_expires_at_sql . ", "
        . $subscription_activated_sql
        . " WHERE user_id = '" . $user_id . "'";

    if (get_result($sql)) {
        $_SESSION['success'] = 'Abo-Status wurde aktualisiert.';
    } else {
        $_SESSION['error'] = 'Abo-Status konnte nicht gespeichert werden.';
    }

    header('Location: subscription_management.php');
    exit();
}

$status_filter = isset($_GET['status']) ? filter_data($_GET['status']) : '';
$search_filter = isset($_GET['q']) ? filter_data($_GET['q']) : '';

$where = " WHERE role = 2 ";

if (in_array($status_filter, array('free', 'active', 'expired'), true)) {
    $where .= " AND subscription_status = '" . $status_filter . "' ";
}

if ($search_filter !== '') {
    $where .= " AND ("
        . "email LIKE '%" . $search_filter . "%' "
        . "OR firstname LIKE '%" . $search_filter . "%' "
        . "OR lastname LIKE '%" . $search_filter . "%') ";
}

$sql_users = "SELECT user_id, firstname, lastname, email, subscription_status, subscription_plan_code, subscription_expires_at, subscription_activated_at "
    . "FROM user "
    . $where
    . "ORDER BY subscription_status ASC, subscription_expires_at ASC, lastname ASC, firstname ASC";

$result_users = get_result($sql_users);
$users = array();
if ($result_users) {
    while ($row = mysqli_fetch_assoc($result_users)) {
        $users[] = $row;
    }
}

function format_dt($value) {
    if ($value === null || $value === '' || $value === '0000-00-00 00:00:00') {
        return '-';
    }
    $ts = strtotime($value);
    if ($ts === false) {
        return '-';
    }
    return date('d.m.Y H:i', $ts);
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
                    <h1>Abo-Verwaltung</h1>
                    <p>Manuelle Freischaltung für Twint-Jahresabo.</p>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Erfolg!</strong> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <strong>Fehler:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="panel panel-default">
                        <div class="panel-heading"><strong>Filter</strong></div>
                        <div class="panel-body">
                            <form method="get" class="form-inline">
                                <div class="form-group" style="margin-right:10px;">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control" style="margin-left:6px;">
                                        <option value="">Alle</option>
                                        <option value="free" <?php echo $status_filter === 'free' ? 'selected' : ''; ?>>free</option>
                                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>active</option>
                                        <option value="expired" <?php echo $status_filter === 'expired' ? 'selected' : ''; ?>>expired</option>
                                    </select>
                                </div>
                                <div class="form-group" style="margin-right:10px;">
                                    <label for="q">Suche</label>
                                    <input type="text" name="q" id="q" class="form-control" value="<?php echo htmlspecialchars($search_filter); ?>" placeholder="Name oder E-Mail" style="margin-left:6px;">
                                </div>
                                <button type="submit" class="btn btn-primary">Anwenden</button>
                                <a href="subscription_management.php" class="btn btn-default">Zurücksetzen</a>
                            </form>
                        </div>
                    </div>

                    <div class="panel panel-info">
                        <div class="panel-heading"><strong>Benutzer-Abos</strong> <span class="badge"><?php echo count($users); ?></span></div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-condensed">
                                    <thead>
                                    <tr>
                                        <th style="min-width: 200px;">Benutzer</th>
                                        <th style="min-width: 170px;">E-Mail</th>
                                        <th style="min-width: 100px;">Status</th>
                                        <th style="min-width: 120px;">Plan</th>
                                        <th style="min-width: 140px;">Ablauf</th>
                                        <th style="min-width: 140px;">Aktiviert</th>
                                        <th style="min-width: 340px;">Ändern</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-muted">Keine Benutzer gefunden.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $entry): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(trim($entry['firstname'] . ' ' . $entry['lastname'])); ?></td>
                                                <td><?php echo htmlspecialchars($entry['email']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['subscription_status']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['subscription_plan_code'] === '' ? '-' : $entry['subscription_plan_code']); ?></td>
                                                <td><?php echo htmlspecialchars(format_dt($entry['subscription_expires_at'])); ?></td>
                                                <td><?php echo htmlspecialchars(format_dt($entry['subscription_activated_at'])); ?></td>
                                                <td>
                                                    <form method="post" class="form-inline" style="display:flex; gap:6px; flex-wrap:wrap;">
                                                        <input type="hidden" name="user_id" value="<?php echo intval($entry['user_id']); ?>">
                                                        <select name="subscription_status" class="form-control input-sm" required>
                                                            <option value="free" <?php echo $entry['subscription_status'] === 'free' ? 'selected' : ''; ?>>free</option>
                                                            <option value="active" <?php echo $entry['subscription_status'] === 'active' ? 'selected' : ''; ?>>active</option>
                                                            <option value="expired" <?php echo $entry['subscription_status'] === 'expired' ? 'selected' : ''; ?>>expired</option>
                                                        </select>
                                                        <input type="text" name="subscription_plan_code" class="form-control input-sm" value="<?php echo htmlspecialchars($entry['subscription_plan_code']); ?>" placeholder="yearly_manual" style="width:120px;">
                                                        <input type="datetime-local" name="subscription_expires_at" class="form-control input-sm" value="<?php echo ($entry['subscription_expires_at'] && $entry['subscription_expires_at'] !== '0000-00-00 00:00:00') ? date('Y-m-d\\TH:i', strtotime($entry['subscription_expires_at'])) : ''; ?>">
                                                        <button type="submit" name="subscription-update" class="btn btn-success btn-sm">Speichern</button>
                                                    </form>
                                                </td>
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
</div>

<?php include './footer.php'; ?>
</body>
</html>
