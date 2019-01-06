<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!--Including all additional CSS and JS files / Modal Window for User / Sidebar collapse-->
    <title>WORTLAB</title>
	<!--**CSS**-->
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- dataTables CSS -->
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-html5-1.5.1/b-print-1.5.1/sl-1.2.5/datatables.min.css"/>
    <!--<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">-->
	<!-- select2 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
	<!-- Custom CSS -->
    <link rel="stylesheet" href="css/custom.css">
	<!--**JS**-->
    <!--jquery JS-->
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <!-- Bootstrap JS -->
    <script src="js/bootstrap.min.js"></script>
	<!-- dataTables JS -->
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/b-1.5.1/b-html5-1.5.1/b-print-1.5.1/sl-1.2.5/datatables.min.js"></script>
    <!--<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>-->
	<!-- select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>
    <style>
        .select2-selection,.select2-container,.select2-container .selection{
            width:100% !important;
        }
    </style>
    <script type="text/javascript">
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
    $result = update_user($user_id, $email, $password, $confirm_password, $gender, $firstname, $lastname, $image_name);
}
$result = get_user($user_id);
$user = mysqli_fetch_assoc($result);

$update_time = date_parse($user['update_time']);
$last_update = $update_time['day'] . "." . $update_time['month'] . "." . $update_time['year'];
?>
<!--Modal window for profile-->
<div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="profil-wortlab">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <div class="modal-header">
                    <h4 class="modal-title" id="profil-wortlab">Persönliche Einstellungen</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="Gender" class="col-sm-2 form-control-label">Anrede</label>
                        <div class="col-sm-5">
                            <select class="form-control form-control-sm" id="Gender" name="gender">
                                <option <?php if ($user['gender'] == "") echo "selected"; ?> value="">--</option>
                                <option <?php if ($user['gender'] == "Frau") echo "selected"; ?> value="Frau">Frau</option>
                                <option <?php if ($user['gender'] == "Herr") echo "selected"; ?> value="Herr">Herr</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Vorname" class="col-sm-2 col-xs-12 form-control-label">Name</label>
                        <div class="col-sm-5 col-xs-6">
                            <input  type="text" class="form-control form-control-sm"
                                    id="Vorname" placeholder="Vorname"
                                    name="firstname" value="<?php echo $user['firstname']; ?>">
                        </div>
                        <div class="col-sm-5 col-xs-6">
                            <input  type="text" class="form-control form-control-sm"
                                    id="Nachname" placeholder="Nachname"
                                    name="lastname" value="<?php echo $user['lastname']; ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Email" class="col-sm-2 form-control-label">E-Mail</label>
                        <div class="col-sm-10">
                            <input  type="email" class="form-control form-control-sm"
                                    id="Email" placeholder="E-Mail" required
                                    name="email" value="<?php echo $user['email']; ?>">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Passwort" class="col-sm-2 form-control-label">Passwort</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control form-control-sm" id="Passwort" placeholder="Passwort" name="password">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Passwort_Conf" class="col-sm-2 form-control-label">Passwort bestätigen</label>
                        <div class="col-sm-10">
                            <input type="password" class="form-control form-control-sm" id="Passwort_Conf" placeholder="Passwort" name="confirm-password">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="img" class="col-sm-2 form-control-label">Profilbild</label>
                        <div class="col-sm-10">
                            <input type="file" name="profil_img">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-success btn-sm" name="update-submit">Änderungen speichern</button>
                </div>
            </form>
        </div><!-- /modal content-->
    </div><!-- /modal dialog-->
</div><!-- /modal user window-->