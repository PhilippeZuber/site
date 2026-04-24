<?php
require_once('system/data.php');
require_once('system/security.php');

$unsubscribe_secret_user = 'wortlab_unsub_v1';
$unsubscribe_secret_job = 'wortlab_job_unsub_v1';
$message = 'Ungültiger Abmelde-Link.';
$status = 'danger';
$can_unsubscribe = false;
$user_id = null;
$contact_id = null;
$token = '';
$unsubscribe_type = 'user';

function build_unsubscribe_token($type, $id, $email, $secret) {
    return hash('sha256', $type . '|' . $id . '|' . $email . '|' . $secret);
}

if (isset($_GET['uid']) && isset($_GET['token'])) {
    $user_id = intval(filter_data($_GET['uid']));
    $token = filter_data($_GET['token']);

    if ($user_id > 0 && $token !== '') {
        $result = get_user($user_id);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            $expected_token = build_unsubscribe_token('user', $user_id, $user['email'], $unsubscribe_secret_user);

            if (hash_equals($expected_token, $token)) {
                if ($user['news'] === 'on') {
                    if (isset($_POST['confirm_unsubscribe'])) {
                        $sql = "UPDATE user SET news = '' WHERE user_id = $user_id";
                        get_result($sql);
                        $message = 'Sie wurden erfolgreich vom Newsletter abgemeldet.';
                        $status = 'success';
                    } else {
                        $message = 'Möchten Sie sich wirklich vom Newsletter abmelden?';
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
} elseif (isset($_GET['cid']) && isset($_GET['token'])) {
    $unsubscribe_type = isset($_GET['type']) ? filter_data($_GET['type']) : 'job_contact';
    $contact_id = intval(filter_data($_GET['cid']));
    $token = filter_data($_GET['token']);

    if ($unsubscribe_type === 'job_contact' && $contact_id > 0 && $token !== '') {
        $sql = "SELECT id, email, status FROM job_pdf_contacts WHERE id = $contact_id LIMIT 1";
        $result = get_result($sql);
        $contact = $result ? mysqli_fetch_assoc($result) : null;

        if ($contact) {
            $expected_token = build_unsubscribe_token('job_contact', $contact_id, $contact['email'], $unsubscribe_secret_job);
            if (hash_equals($expected_token, $token)) {
                if ($contact['status'] !== 'unsubscribed') {
                    if (isset($_POST['confirm_unsubscribe'])) {
                        $sql_update = "UPDATE job_pdf_contacts SET status = 'unsubscribed', last_seen_at = NOW() WHERE id = $contact_id";
                        get_result($sql_update);
                        $message = 'Sie wurden erfolgreich von weiteren Informationen abgemeldet.';
                        $status = 'success';
                    } else {
                        $message = 'Möchten Sie sich wirklich von weiteren Informationen abmelden?';
                        $status = 'info';
                        $can_unsubscribe = true;
                    }
                } else {
                    $message = 'Sie sind bereits von weiteren Informationen abgemeldet.';
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
                    <?php if ($unsubscribe_type === 'job_contact'): ?>
                        <form method="POST" action="unsubscribe.php?cid=<?php echo htmlspecialchars($contact_id); ?>&type=job_contact&token=<?php echo htmlspecialchars($token); ?>">
                    <?php else: ?>
                        <form method="POST" action="unsubscribe.php?uid=<?php echo htmlspecialchars($user_id); ?>&token=<?php echo htmlspecialchars($token); ?>">
                    <?php endif; ?>
                        <button type="submit" name="confirm_unsubscribe" class="btn btn-danger">Abmelden</button>
                        <a href="https://wortlab.ch" class="btn btn-default">Zurück zur Website</a>
                    </form>
                <?php else: ?>
                    <a href="https://wortlab.ch" class="btn btn-primary">Zurück zur Website</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
