<?php
require_once('system/data.php');
require_once('system/security.php');

$unsubscribe_secret = 'wortlab_unsub_v1';
$message = 'Ungültiger Abmelde-Link.';
$status = 'danger';
$can_unsubscribe = false;
$user_id = null;
$token = '';

function build_unsubscribe_token($user_id, $email, $secret) {
    return hash('sha256', $user_id . '|' . $email . '|' . $secret);
}

if (isset($_GET['uid']) && isset($_GET['token'])) {
    $user_id = intval(filter_data($_GET['uid']));
    $token = filter_data($_GET['token']);

    if ($user_id > 0 && $token !== '') {
        $result = get_user($user_id);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            $expected_token = build_unsubscribe_token($user_id, $user['email'], $unsubscribe_secret);

            if (hash_equals($expected_token, $token)) {
                if ($user['news'] === 'on') {
                    if (isset($_POST['confirm_unsubscribe'])) {
                        $sql = "UPDATE user SET news = '' WHERE user_id = $user_id";
                        get_result($sql);
                        $message = 'Sie wurden erfolgreich vom Newsletter abgemeldet.';
                        $status = 'success';
                    } else {
                        $message = 'Moechten Sie sich wirklich vom Newsletter abmelden?';
                        $status = 'info';
                        $can_unsubscribe = true;
                    }
                } else {
                    $message = 'Sie sind bereits vom Newsletter abgemeldet.';
                    $status = 'info';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter abmelden - WORTLAB</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body>
    <div class="container" style="margin-top: 40px; max-width: 720px;">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Newsletter abmelden</h3>
            </div>
            <div class="panel-body">
                <div class="alert alert-<?php echo htmlspecialchars($status); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>

                <?php if ($can_unsubscribe): ?>
                    <form method="POST" action="unsubscribe.php?uid=<?php echo htmlspecialchars($user_id); ?>&token=<?php echo htmlspecialchars($token); ?>">
                        <button type="submit" name="confirm_unsubscribe" class="btn btn-danger">Abmelden</button>
                        <a href="https://wortlab.ch" class="btn btn-default">Zurueck zur Website</a>
                    </form>
                <?php else: ?>
                    <a href="https://wortlab.ch" class="btn btn-primary">Zurueck zur Website</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
