<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');
require_once('system/addin_auth.php');

$ttl_seconds = 60 * 60 * 8; // 8 Stunden
$token = issue_addin_token($user_id, $ttl_seconds);

$page = 'get_addin_token';
include('header.php');
?>

<div class="container" style="max-width:640px;margin-top:40px;">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title"><span class="glyphicon glyphicon-link"></span> Wortlab Word Add-in &ndash; Bearer-Token</h3>
        </div>
        <div class="panel-body">
            <p class="text-muted">Eingeloggt als User-ID <strong><?php echo intval($_SESSION['id']); ?></strong>. Token gültig für <strong>8 Stunden</strong>.</p>

            <div class="form-group">
                <label>Bearer-Token (kopieren und ins Add-in einfügen)</label>
                <textarea id="token-field" class="form-control" rows="5" readonly style="font-family:monospace;font-size:11px;word-break:break-all;"><?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>

            <button class="btn btn-primary" onclick="copyToken()">
                <span class="glyphicon glyphicon-copy"></span> Token kopieren
            </button>
            <span id="copy-msg" style="margin-left:10px;color:#3c763d;display:none;">Kopiert!</span>

            <hr>
            <p class="text-muted" style="font-size:12px;">
                <strong>API-Basis:</strong> <code>https://wortlab.ch/api/v1</code><br>
                Diesen Token im Add-in unter «Verbindung» einfügen und «Verbindung testen» klicken.
            </p>
        </div>
    </div>
</div>

<script>
function copyToken() {
    var field = document.getElementById('token-field');
    field.select();
    field.setSelectionRange(0, 99999);
    try {
        document.execCommand('copy');
        document.getElementById('copy-msg').style.display = 'inline';
        setTimeout(function() {
            document.getElementById('copy-msg').style.display = 'none';
        }, 2000);
    } catch (e) {
        alert('Manuell kopieren: Strg+A dann Strg+C im Textfeld.');
    }
}
</script>

<?php include('footer.php'); ?>
