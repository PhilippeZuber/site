<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
    exit;
}
$user_id = $_SESSION['id'];
require_once('system/data.php');
require_once('system/security.php');

$page = 'search';

// Get word IDs from URL
$ids_raw = isset($_GET['ids']) ? filter_data($_GET['ids']) : '';
$ids_raw = preg_replace('/[^0-9,]/', '', $ids_raw);

// Get layout type
$layout = isset($_GET['layout']) ? filter_data($_GET['layout']) : 'cards';
$layout = in_array($layout, array('cards', 'list', 'memory', 'bingo', 'syllables')) ? $layout : 'cards';

// Fetch words
$words = array();
if ($ids_raw != '') {
    $id_list = explode(',', $ids_raw);
    $id_list = array_filter($id_list);
    $id_list = array_map('intval', $id_list);
    
    if (count($id_list) > 0) {
        $id_str = implode(',', $id_list);
        $sql = "SELECT id, name, image, image_url FROM words WHERE id IN ($id_str)";
        $result = get_result($sql);
        while ($row = mysqli_fetch_assoc($result)) {
            // Prioritize local image, fallback to URL
            $image_src = '';
            if (!empty($row['image'])) {
                $image_src = 'images/' . $row['image'];
            } elseif (!empty($row['image_url'])) {
                $image_src = $row['image_url'];
            }
            $words[] = array(
                'id' => $row['id'],
                'name' => $row['name'],
                'image' => $image_src
            );
        }
    }
}

// Layout configurations
$layouts = array(
    'cards' => array('name' => 'Bildkarten (4 pro Seite)', 'icon' => 'th-large'),
    'list' => array('name' => 'Wortliste', 'icon' => 'list'),
    'memory' => array('name' => 'Memory-Karten', 'icon' => 'duplicate'),
    'bingo' => array('name' => 'Bingo-Karte (3x3)', 'icon' => 'th'),
    'syllables' => array('name' => 'Silbenkarten', 'icon' => 'scissors')
);
?>
<!DOCTYPE html>
<html lang="de">
    <?php include './header.php'; ?>
    <style>
        /* Screen styles */
        .layout-selector {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .layout-btn {
            margin: 5px;
        }
        .no-print {
            display: block;
        }
        
        /* Print styles */
        @media print {
            .wrapper, #content, .container-fluid {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            .no-print, nav, .sidebar, .navbar, .btn, .layout-selector, h1, .alert {
                display: none !important;
            }
            body {
                background: white;
                margin: 0;
                padding: 10mm;
            }
            
            /* Bildkarten Layout */
            .worksheet-cards .word-card {
                width: 48%;
                height: 48vh;
                float: left;
                margin: 1%;
                page-break-inside: avoid;
                border: 1px solid #ddd;
                padding: 10px;
                box-sizing: border-box;
                text-align: center;
            }
            .worksheet-cards .word-card img {
                max-width: 90%;
                max-height: 35vh;
                object-fit: contain;
            }
            .worksheet-cards .word-card .word-name {
                font-size: 28pt;
                font-weight: bold;
                margin-top: 10px;
            }
            
            /* Liste Layout */
            .worksheet-list {
                column-count: 2;
                column-gap: 20px;
            }
            .worksheet-list .word-item {
                font-size: 18pt;
                line-height: 2;
                page-break-inside: avoid;
            }
            
            /* Memory Layout */
            .worksheet-memory .memory-card {
                width: 48%;
                height: 45vh;
                float: left;
                margin: 1%;
                page-break-inside: avoid;
                border: 2px dashed #999;
                padding: 15px;
                box-sizing: border-box;
                text-align: center;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .worksheet-memory .memory-card img {
                max-width: 90%;
                max-height: 35vh;
                object-fit: contain;
            }
            .worksheet-memory .memory-card .word-name {
                font-size: 28pt;
                font-weight: bold;
                margin: 0;
                padding: 0;
            }
            
            /* Memory card backs (for cutting guide) */
            .worksheet-memory .page-break {
                page-break-after: always;
                clear: both;
            }
            
            /* Bingo Layout */
            .worksheet-bingo {
                width: 100%;
                border-collapse: collapse;
            }
            .worksheet-bingo td {
                width: 33.33%;
                height: 28vh;
                border: 2px solid #000;
                text-align: center;
                vertical-align: middle;
                padding: 10px;
            }
            .worksheet-bingo td img {
                max-width: 90%;
                max-height: 20vh;
                object-fit: contain;
            }
            .worksheet-bingo td .word-name {
                font-size: 18pt;
                font-weight: bold;
                margin-top: 5px;
            }
            
            /* Silbenkarten Layout */
            .worksheet-syllables .syllable-card {
                width: 48%;
                height: 48vh;
                float: left;
                margin: 1%;
                page-break-inside: avoid;
                border: 1px solid #ddd;
                padding: 10px;
                box-sizing: border-box;
                text-align: center;
            }
            .worksheet-syllables .syllable-card img {
                max-width: 90%;
                max-height: 30vh;
                object-fit: contain;
            }
            .worksheet-syllables .syllable-card .word-name {
                font-size: 28pt;
                font-weight: bold;
                margin-top: 10px;
            }
            .worksheet-syllables .syllable-card .syllables {
                font-size: 24pt;
                color: #0066cc;
                margin-top: 5px;
                letter-spacing: 3px;
            }
        }
        
        /* Screen preview styles */
        .word-card, .syllable-card, .memory-card {
            border: 2px dashed #999;
            padding: 15px;
            margin: 10px;
            text-align: center;
            display: inline-block;
            width: 280px;
            height: 350px;
            vertical-align: top;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .memory-card {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .word-card img, .syllable-card img, .memory-card img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
        }
        .word-name {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
        }
        .syllables {
            font-size: 20px;
            color: #0066cc;
            margin-top: 5px;
        }
        .word-item {
            font-size: 18px;
            padding: 5px 0;
        }
        .worksheet-bingo {
            border-collapse: collapse;
            margin: 20px auto;
        }
        .worksheet-bingo td {
            border: 2px solid #000;
            padding: 20px;
            text-align: center;
            width: 200px;
            height: 200px;
        }
        .worksheet-bingo td img {
            max-width: 150px;
            max-height: 150px;
        }
    </style>
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class="container-fluid">
                    <div class="row no-print">
                        <div class="col-md-12">
                            <h1>Arbeitsblatt erstellen</h1>
                            <?php if (empty($words)) { ?>
                                <div class="alert alert-warning">
                                    Keine Wörter ausgewählt. Bitte gehe zurück zur <a href="search.php">Suche</a> und wähle Wörter aus.
                                </div>
                            <?php } else { ?>
                                <div class="alert alert-info">
                                    <strong><?php echo count($words); ?> Wörter</strong> ausgewählt für Arbeitsblatt
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($words)) { ?>
                    <div class="row no-print">
                        <div class="col-md-12">
                            <div class="layout-selector">
                                <strong>Layout auswählen:</strong><br>
                                <?php foreach ($layouts as $key => $value) { ?>
                                    <a href="?ids=<?php echo urlencode($ids_raw); ?>&layout=<?php echo $key; ?>" 
                                       class="btn btn-<?php echo $layout == $key ? 'primary' : 'default'; ?> layout-btn">
                                        <span class="glyphicon glyphicon-<?php echo $value['icon']; ?>"></span>
                                        <?php echo $value['name']; ?>
                                    </a>
                                <?php } ?>
                                <button onclick="window.print();" class="btn btn-success layout-btn">
                                    <span class="glyphicon glyphicon-print"></span> Drucken / Als PDF speichern
                                </button>
                                <a href="search.php" class="btn btn-link layout-btn">Zurück zur Suche</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Simple syllable splitting (basic German rules)
                            function split_syllables($word) {
                                $word_lower = mb_strtolower($word);
                                // Basic approach: split between consonant+vowel
                                $vowels = array('a', 'e', 'i', 'o', 'u', 'ä', 'ö', 'ü');
                                $syllables = array();
                                $current = '';
                                
                                // Very simple: just add hyphens at logical points
                                // This is a placeholder - real syllabification needs linguistic rules
                                $result = $word;
                                $len = mb_strlen($word_lower);
                                
                                // Simple rule: insert · between consonants followed by vowel
                                // This is very basic and would need proper implementation
                                return $result;
                            }
                            
                            switch ($layout) {
                                case 'cards':
                                    // Bildkarten Layout
                                    echo '<div class="worksheet-cards">';
                                    foreach ($words as $word) {
                                        echo '<div class="word-card">';
                                        if (!empty($word['image'])) {
                                            echo '<img src="' . htmlspecialchars($word['image']) . '" alt="' . htmlspecialchars($word['name']) . '">';
                                        }
                                        echo '<div class="word-name">' . htmlspecialchars($word['name']) . '</div>';
                                        echo '</div>';
                                    }
                                    echo '<div style="clear:both;"></div></div>';
                                    break;
                                    
                                case 'list':
                                    // Wortliste Layout (nur Text, keine Bilder)
                                    echo '<div class="worksheet-list">';
                                    $counter = 1;
                                    foreach ($words as $word) {
                                        echo '<div class="word-item">' . $counter . '. ' . htmlspecialchars($word['name']) . '</div>';
                                        $counter++;
                                    }
                                    echo '</div>';
                                    break;
                                    
                                case 'memory':
                                    // Memory-Karten Layout (zeigt Bild und Wort getrennt)
                                    echo '<div class="worksheet-memory">';
                                    $card_count = 0;
                                    foreach ($words as $word) {
                                        // Bild-Karte
                                        echo '<div class="memory-card">';
                                        if (!empty($word['image'])) {
                                            echo '<img src="' . htmlspecialchars($word['image']) . '" alt="' . htmlspecialchars($word['name']) . '">';
                                        } else {
                                            echo '<div style="font-size: 80px; color: #ccc;">?</div>';
                                        }
                                        echo '</div>';
                                        
                                        // Wort-Karte
                                        echo '<div class="memory-card">';
                                        echo '<div class="word-name">' . htmlspecialchars($word['name']) . '</div>';
                                        echo '</div>';
                                        
                                        $card_count += 2;
                                        // Seitenumbruch nach 4 Karten (2 Paare pro Seite)
                                        if ($card_count % 4 == 0 && $card_count < count($words) * 2) {
                                            echo '<div class="page-break"></div>';
                                        }
                                    }
                                    echo '<div style="clear:both;"></div></div>';
                                    break;
                                    
                                case 'bingo':
                                    // Bingo-Karte Layout (3x3)
                                    echo '<table class="worksheet-bingo">';
                                    $bingo_words = array_slice($words, 0, 9); // Max 9 words for 3x3
                                    shuffle($bingo_words); // Randomize
                                    
                                    for ($row = 0; $row < 3; $row++) {
                                        echo '<tr>';
                                        for ($col = 0; $col < 3; $col++) {
                                            $index = $row * 3 + $col;
                                            echo '<td>';
                                            if (isset($bingo_words[$index])) {
                                                $word = $bingo_words[$index];
                                                if (!empty($word['image'])) {
                                                    echo '<img src="' . htmlspecialchars($word['image']) . '" alt="' . htmlspecialchars($word['name']) . '">';
                                                }
                                                echo '<div class="word-name">' . htmlspecialchars($word['name']) . '</div>';
                                            }
                                            echo '</td>';
                                        }
                                        echo '</tr>';
                                    }
                                    echo '</table>';
                                    break;
                                    
                                case 'syllables':
                                    // Silbenkarten Layout
                                    echo '<div class="worksheet-syllables">';
                                    foreach ($words as $word) {
                                        echo '<div class="syllable-card">';
                                        if (!empty($word['image'])) {
                                            echo '<img src="' . htmlspecialchars($word['image']) . '" alt="' . htmlspecialchars($word['name']) . '">';
                                        }
                                        echo '<div class="word-name">' . htmlspecialchars($word['name']) . '</div>';
                                        echo '<div class="syllables">' . split_syllables($word['name']) . '</div>';
                                        echo '</div>';
                                    }
                                    echo '<div style="clear:both;"></div></div>';
                                    break;
                            }
                            ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php include './footer.php'; ?>
    </body>
</html>
