<?php
require_once('system/data.php');
require_once('system/security.php');


$kanton = $_REQUEST['kanton'];
$institution = $_REQUEST['institution'];

$ausgabe = get_selection($kanton, $institution);
$echo_ok = true;

echo "<h2>Aktuelle Stellen:</h2><br>";

        while($output = mysqli_fetch_assoc($ausgabe)){    //Die erhaltenen Werte müssen in ein Array umgewandelt werden
        echo "$output[name]<br>";
    }

    $row_count = mysqli_num_rows($ausgabe);

                if($row_count == 0){
                    echo "Zu diesen Kriterien haben wir zurzeit keine Stellenanzeigen";
                }
 ?>
