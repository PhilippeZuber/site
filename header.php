<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>WORTLAB</title>
  <!--Including all additional CSS and JS files / Modal Window for User / Sidebar collapse-->
	<!--**CSS**-->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <!-- dataTables CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-html5-1.5.1/b-print-1.5.1/sl-1.2.5/datatables.min.css"/>
	<!-- select2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
	  <!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
	<!--**JS**-->
    <!--jquery JS-->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	<!-- dataTables JS -->
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script defer src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-html5-1.5.1/b-print-1.5.1/sl-1.2.5/datatables.min.js"></script>
    <!--<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>-->
	<!-- select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>
    <!-- custom JS -->
    <script src="js/custom.js"></script>
    <style>
        .select2-selection,.select2-container,.select2-container .selection{
            width:100% !important;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
            });
        });
    </script>
</head>
<?php
/* Update User */
if (isset($_POST['update-submit'])) {
    $email = filter_data($_POST['email']);
    $password = filter_data($_POST['password']);
    $confirm_password = filter_data($_POST['confirm-password']);
    $gender = filter_data($_POST['gender']);
    $firstname = filter_data($_POST['firstname']);
    $lastname = filter_data($_POST['lastname']);
    $image_name = "";
    $job = filter_data($_POST['job']);
    $canton = filter_data($_POST['canton']);


    $result = get_user($user_id);
    $user = mysqli_fetch_assoc($result);
    $image_name = $user['img_src'];

    $upload_path = "user_img/";
    $max_file_size = 500000;
    $upload_ok = true;

    if ($_FILES['profil_img']['name'] != "") {
        $filetype = $_FILES['profil_img']['type'];
        switch ($filetype) {
            case "image/jpg":
                $file_extension = "jpg";
                break;
            case "image/jpeg":
                $file_extension = "jpg";
                break;
            case "image/gif":
                $file_extension = "gif";
                break;
            case "image/png":
                $file_extension = "png";
                break;
            default:
                $upload_ok = false;
        }

        $upload_filesize = $_FILES['profil_img']['size'];
        if ($upload_filesize >= $max_file_size) {
            $upload_ok = false;
            echo "Leider ist die Datei mit $upload_filesize KB zu gross. <br> Sie darf nicht grösser als $max_file_size sein. ";
        }

        if ($upload_ok) {
            $image_name = time() . "_" . $user['user_id'] . "." . $file_extension;
			      move_uploaded_file($_FILES['profil_img']['tmp_name'], $upload_path . $image_name);
        } else {
            echo "Leider konnte die Datei nicht hochgeladen werden. ";
        }
    }
    $result = update_user($user_id, $email, $password, $confirm_password, $gender, $firstname, $lastname, $image_name, $job, $canton);
}
$result = get_user($user_id);
$user = mysqli_fetch_assoc($result);

$update_time = date_parse($user['update_time']);
$last_update = $update_time['day'] . "." . $update_time['month'] . "." . $update_time['year'];

/* Delete User */
if (isset($_POST['update-delete'])) {
    console.log("Message here");
    $result = delete_user($user_id);
}
?>
<!--Modal window for profile-->
<div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="profil-wortlab">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Schliessen"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="profil-wortlab">Profil ändern</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <select class="form-control form-control-sm" id="Gender" name="gender" tabindex="1">
                                <option <?php if ($user['gender'] == "") echo "selected"; ?> value="">--</option>
                                <option <?php if ($user['gender'] == "Frau") echo "selected"; ?> value="Frau">Frau</option>
                                <option <?php if ($user['gender'] == "Herr") echo "selected"; ?> value="Herr">Herr</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input  type="text" class="form-control form-control-sm" tabindex="2"
                                    id="Vorname" placeholder="Vorname"
                                    name="firstname" value="<?php echo $user['firstname']; ?>">
                        </div>
                        <div class="col-sm-6">
                            <input  type="text" class="form-control form-control-sm" tabindex="3"
                                    id="Nachname" placeholder="Nachname"
                                    name="lastname" value="<?php echo $user['lastname']; ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-sm-6">
                        <select class="form-control form-control-sm" id="job" name="job" tabindex="4">
                          <option <?php if ($user['job'] == "Anderes") echo "selected"; ?> value="Anderes">Anderes</option>
                          <option <?php if ($user['job'] == "LogopädIn") echo "selected"; ?> value="LogopädIn">LogopädIn B.A.</option>
                          <option <?php if ($user['job'] == "LehrerIn") echo "selected"; ?> value="LehrerIn">LehrerIn</option>
                        </select>
                      </div>
                      <div class="col-sm-6">
                        <select class="form-control form-control-sm" id="canton" name="canton" tabindex="5">
                          <option value="ag">Aargau</option>
                          <option value="ar">Appenzell Ausserrhoden</option>
                          <option value="ai">Appenzell Innerrhoden</option>
                          <option value="bl">Basel-Landschaft</option>
                          <option value="bs">Basel-Stadt</option>
                          <option value="be">Bern</option>
                          <option value="fr">Freiburg</option>
                          <option value="ge">Genf</option>
                          <option value="gl">Glarus</option>
                          <option value="gr">Graubünden</option>
                          <option value="ju">Jura</option>
                          <option value="lu">Luzern</option>
                          <option value="ne">Neuenburg</option>
                          <option value="nw">Nidwalden</option>
                          <option value="ow">Obwalden</option>
                          <option value="sh">Schaffhausen</option>
                          <option value="sz">Schwyz</option>
                          <option value="so">Solothurn</option>
                          <option value="sg">St. Gallen</option>
                          <option value="ti">Tessin</option>
                          <option value="tg">Thurgau</option>
                          <option value="ur">Uri</option>
                          <option value="vd">Waadt</option>
                          <option value="vs">Wallis</option>
                          <option value="zg">Zug</option>
                          <option value="zh">Zürich</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <input  type="email" class="form-control form-control-sm" tabindex="6"
                                    id="Email" placeholder="E-Mail" required
                                    name="email" value="<?php echo $user['email']; ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input type="password" class="form-control form-control-sm" id="Passwort" placeholder="Passwort" name="password" tabindex="7">
                        </div>
                        <div class="col-sm-6">
                            <input type="password" class="form-control form-control-sm" id="Passwort_Conf" placeholder="Passwort" name="confirm-password" tabindex="8">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-12">
                            <div>Profilbild auswählen <input type="file" name="profil_img"></div>
                        </div>
                    </div>
                    <!--<button type="submit" class="btn btn-danger btn-sm" data-dismiss="modal" name="update-delete">Mein Profil löschen</button>-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-success btn-sm" name="update-submit">Änderungen speichern</button>
                </div>
            </form>
        </div><!-- /modal content-->
    </div><!-- /modal dialog-->
</div><!-- /modal user window-->