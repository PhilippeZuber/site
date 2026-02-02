<?php
require_once('system/data.php');
require_once('system/security.php');

// Kantonsabkürzungen zu Vollnamen
$kantone = array(
    'ag' => 'Aargau',
    'ar' => 'Appenzell Ausserrhoden',
    'ai' => 'Appenzell Innerrhoden',
    'bl' => 'Basel-Landschaft',
    'bs' => 'Basel-Stadt',
    'be' => 'Bern',
    'fr' => 'Freiburg',
    'ge' => 'Genf',
    'gl' => 'Glarus',
    'gr' => 'Graubünden',
    'ju' => 'Jura',
    'lu' => 'Luzern',
    'ne' => 'Neuenburg',
    'nw' => 'Nidwalden',
    'ow' => 'Obwalden',
    'sh' => 'Schaffhausen',
    'sz' => 'Schwyz',
    'so' => 'Solothurn',
    'sg' => 'St. Gallen',
    'ti' => 'Tessin',
    'tg' => 'Thurgau',
    'ur' => 'Uri',
    'vd' => 'Waadt',
    'vs' => 'Wallis',
    'zg' => 'Zug',
    'zh' => 'Zürich'
);

$kanton = filter_data($_REQUEST['kanton']);
$institution = filter_data($_REQUEST['institution']);

$ausgabe = get_selection($kanton, $institution);

echo "<h2>Aktuelle Stellen:</h2><br>";

$row_count = mysqli_num_rows($ausgabe);

if($row_count > 0) {
    while($output = mysqli_fetch_assoc($ausgabe)){
        echo "<div style='border-bottom: 1px solid #ccc; padding-bottom: 15px; margin-bottom: 15px;'>";
        echo "<h4 style='margin: 0 0 5px 0; color: #0066cc;'>" . htmlspecialchars($output['name']) . "</h4>";
        
        // Kanton mit Vollname anzeigen
        $kantons_name = isset($kantone[$output['kanton']]) ? $kantone[$output['kanton']] : htmlspecialchars($output['kanton']);
        echo "<p style='margin: 3px 0;'><strong>Kanton:</strong> " . $kantons_name . "</p>";
        
        echo "<p style='margin: 3px 0;'><strong>Institution:</strong> " . htmlspecialchars($output['institution']) . "</p>";
        echo "<p style='margin: 3px 0;'><strong>Stellenantritt:</strong> " . htmlspecialchars($output['stellenantritt']) . "</p>";
        if(!empty($output['erscheinen_am'])) {
            $datum = date('d.m.Y', strtotime($output['erscheinen_am']));
            echo "<p style='margin: 3px 0;'><strong>Erschienen am:</strong> " . htmlspecialchars($datum) . "</p>";
        }
        
        // PDF-Link anzeigen wenn vorhanden
        if(!empty($output['pdf_url'])) {
            echo "<p style='margin: 3px 0;'>";
            echo "<a href='" . htmlspecialchars($output['pdf_url']) . "' target='_blank' class='btn btn-sm btn-primary'>";
            echo "<span class='glyphicon glyphicon-download'></span> PDF Stellenanzeige herunterladen";
            echo "</a></p>";
        }
        
        echo "</div>";
    }
} else {
    echo "Zu diesen Kriterien haben wir zurzeit keine Stellenanzeigen";
}
?>