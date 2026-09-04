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

$page = 'canton_flatrate_management';

$cantons = array(
    'ag' => 'Aargau', 'ar' => 'Appenzell Ausserrhoden', 'ai' => 'Appenzell Innerrhoden',
    'bl' => 'Basel-Landschaft', 'bs' => 'Basel-Stadt', 'be' => 'Bern', 'fr' => 'Freiburg',
    'ge' => 'Genf', 'gl' => 'Glarus', 'gr' => 'Graubünden', 'ju' => 'Jura', 'lu' => 'Luzern',
    'ne' => 'Neuenburg', 'nw' => 'Nidwalden', 'ow' => 'Obwalden', 'sh' => 'Schaffhausen',
    'sz' => 'Schwyz', 'so' => 'Solothurn', 'sg' => 'St. Gallen', 'ti' => 'Tessin',
    'tg' => 'Thurgau', 'ur' => 'Uri', 'vd' => 'Waadt', 'vs' => 'Wallis', 'zg' => 'Zug', 'zh' => 'Zürich'
);

if (isset($_POST['flatrate-add'])) {
    $canton = isset($_POST['canton']) ? filter_data($_POST['canton']) : '';
    $label = isset($cantons[$canton]) ? $cantons[$canton] : $canton;

    if (!array_key_exists($canton, $cantons)) {
        $_SESSION['error'] = 'Ungültiger Kanton.';
    } elseif (add_flatrate_canton($canton, $label)) {
        $_SESSION['success'] = 'Kanton wurde hinzugefügt.';
    } else {
        $_SESSION['error'] = 'Kanton konnte nicht hinzugefügt werden (evtl. bereits vorhanden).';
    }

    header('Location: canton_flatrate_management.php');
    exit();
}

if (isset($_POST['flatrate-delete'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id > 0 && delete_flatrate_canton($id)) {
        $_SESSION['success'] = 'Kanton wurde entfernt.';
    } else {
        $_SESSION['error'] = 'Kanton konnte nicht entfernt werden.';
    }

    header('Location: canton_flatrate_management.php');
    exit();
}

$flatrate_cantons = get_flatrate_cantons();
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
                    <h1>Kantons-Pauschale Add-in</h1>
                    <p>Kantone in dieser Liste erhalten automatisch Add-in-Zugriff für alle registrierten Nutzer, unabhängig vom Abo-Status.</p>

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
                        <div class="panel-heading"><strong>Kanton hinzufügen</strong></div>
                        <div class="panel-body">
                            <form method="post" class="form-inline">
                                <div class="form-group" style="margin-right:10px;">
                                    <select name="canton" class="form-control" required>
                                        <?php foreach ($cantons as $code => $name): ?>
                                            <option value="<?php echo htmlspecialchars($code); ?>"><?php echo htmlspecialchars($name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="flatrate-add" class="btn btn-primary">Hinzufügen</button>
                            </form>
                        </div>
                    </div>

                    <div class="panel panel-info">
                        <div class="panel-heading"><strong>Kantone mit Pauschale</strong> <span class="badge"><?php echo count($flatrate_cantons); ?></span></div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-condensed">
                                    <thead>
                                    <tr>
                                        <th>Kanton</th>
                                        <th>Bezeichnung</th>
                                        <th>Hinzugefügt</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (empty($flatrate_cantons)): ?>
                                        <tr>
                                            <td colspan="4" class="text-muted">Keine Kantone hinterlegt.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($flatrate_cantons as $entry): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars(strtoupper($entry['canton'])); ?></td>
                                                <td><?php echo htmlspecialchars($entry['label']); ?></td>
                                                <td><?php echo htmlspecialchars($entry['created_at']); ?></td>
                                                <td>
                                                    <form method="post" onsubmit="return confirm('Kanton wirklich entfernen?');">
                                                        <input type="hidden" name="id" value="<?php echo intval($entry['id']); ?>">
                                                        <button type="submit" name="flatrate-delete" class="btn btn-danger btn-xs">Entfernen</button>
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
