<?php

function get_db_connection() {
    $db = mysqli_connect('localhost', 'wortlabor', 'Wortlabor01$', 'wortlabor')
            or die('Fehler beim Verbinden mit dem Datenbank-Server.');
    mysqli_set_charset($db, "utf8");
    return $db;
}

function get_result($sql) {
    $db = get_db_connection();
    // echo $sql;
    $result = mysqli_query($db, $sql);
    mysqli_close($db);
    return $result;
}

function get_id_result($sql) {
    $db = get_db_connection();
    // echo $sql;
    mysqli_query($db, $sql);
    $result = mysqli_insert_id($db);
    mysqli_close($db);
    return $result;
}

function get_data_result($sql) {
    $db = get_db_connection();
    // echo $sql;
    $result = mysqli_query($db, $sql);
    $array = array();

    while ($row = mysqli_fetch_array($result)) {
        $array[] = $row;
    }

    
    mysqli_close($db);
    return $array;
}

/* * ******************** */
/* Login & Register */
/* * ******************** */

function login($email, $password) {
    $sql = "SELECT * FROM user WHERE email = '" . $email . "' AND password = '" . $password . "';";
    return get_result($sql);
}

function register($email, $password) {
    $sql = "INSERT INTO user (email, password,role) VALUES ('$email', '$password','2');";
    return get_result($sql);
}

/* * ******************** */
/* Update Profil/
  /********************** */

function get_user($user_id) {
    $sql = "SELECT * FROM user WHERE user_id = $user_id;";
    return get_result($sql);
}

function update_user($user_id, $email, $password, $confirm_password, $gender, $firstname, $lastname, $image_name) {
    $sql_ok = false;
    $sql = "UPDATE user SET ";
    if ($email != "") {
        $sql .= "email = '$email', ";
        $sql_ok = true;
    }
    if ($password != "" && $confirm_password == $password) {
        $sql .= "password = '$password', ";
        $sql_ok = true;
    }
    if ($gender != "") {
        $sql .= "gender = '$gender', ";
        $sql_ok = true;
    }
    if ($firstname != "") {
        $sql .= "firstname = '$firstname', ";
        $sql_ok = true;
    }
    if ($lastname != "") {
        $sql .= "lastname = '$lastname', ";
        $sql_ok = true;
    }
    if($image_name != ""){
      $sql .= "img_src = '$image_name', ";
      $sql_ok = true;
    }
    $sql = substr_replace($sql, ' ', -2, 1);

    $sql .= "WHERE user_id = $user_id;";

    if ($sql_ok) {
        return get_result($sql);
    } else {
        return false;
    }
}

/* * ******************** */
/* Wortarten */
/* * ******************** */

function wortarten() {
    
}

/* * ******************** */
/* Days Berechnung */
/* * ******************** */

function wochentage($days) {
    $wd = "(";
    $n = 0;

    foreach ($days as $day) {
        if ($n > 0) {
            $wd .= " OR ";
        }
        $wd .= " $day = 1 ";
        $n ++;
    }
    $wd .= ")";
    return $wd;
}

function kategorien($categories) {
    $cat = "(";
    $n = 0;

    foreach ($categories as $category) {
        if ($n > 0) {
            $cat .= " OR ";
        }
        $cat .= " $category = 1 ";
        $n ++;
    }
    $cat .= ")";
    return $cat;
}

function aktivitaet_suchen($category, $days, $people) {
    $Aktivitaeten = "SELECT name, beschreibung, bild, MinPersonen, MaxPersonen
	FROM Aktivitaeten
		WHERE $days
		AND $category
		AND (MinPersonen <= $people
			 AND MaxPersonen >= $people)
		ORDER BY RAND();";
    return get_result($Aktivitaeten);
}

function add_record($table, $data) {
    
//    echo '<pre>';
//    print_r($data);
//    die;
    unset($data['id']);
    $sql = "INSERT INTO $table (";
    foreach ($data as $key => $value) {
        $sql .= $key . ",";
    }
    $sql = rtrim($sql, ',');
    $sql .= " ) VALUES ( ";
    foreach ($data as $key => $value) {
        $sql .= "'" . $value . "',";
    }
    $sql = rtrim($sql, ',');
    $sql .= " ) ";

 
    return get_result($sql);
}

function update_record($table, $id, $data) {
    unset($data['id']);


    $sql = "UPDATE $table SET ";
    foreach ($data as $key => $value) {
        $sql .= $key . "='" . $value . "',";
    }
    $sql = rtrim($sql, ',');
    $sql .= " where id='" . $id . "'";

    return get_result($sql);
}

function delete_record($table, $id) {
    $sql = "DELETE  from $table where id='" . $id . "'";
    return get_result($sql);
}

function get_single_record($table, $id) {
    $sql = "SELECT * from $table where id='" . $id . "'";
    $result = get_data_result($sql);
    if (!empty($result)) {
        $result = $result[0];
    }
    return $result;
}

function get_records($table) {


    $sql = "select * from $table";
    return get_data_result($sql);
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function get_words() {
    $sql = "select a.id,a.name,a.image,
        b.name as category ,
        GROUP_CONCAT(c.name ORDER BY d.id) semantic ,
        d.name  alters from words a  
            LEFT JOIN category b ON b.id=a.category
            LEFT JOIN semantic c ON FIND_IN_SET(c.id, a.semantic) > 0 
            LEFT JOIN alters d ON d.id=a.alters group by a.id
            ";
    

    return get_data_result($sql);
}

?>
