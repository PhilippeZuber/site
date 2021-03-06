<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'add_word';

if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit') {
        $word = get_single_record('words', $_GET['id']);
    }
    if ($_GET['action'] == 'delete') {
        $word = delete_record('words', $_GET['id']);
        header("Location:add_word.php");
    }
}
if (isset($_POST['data'])) {
    $image = '';
    $file = $_FILES['image'];


    if (isset($file) && !empty($file)) {

        $destinationPath = getcwd() . '/images/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = generateRandomString(25) . '.' . $ext;
        $allowed = array('gif', 'png', 'jpg', 'jpeg', 'Jpeg', 'JPG', 'PNG', 'GIF');
        if (in_array($ext, $allowed)) {
            $mm = substr($mime, 0, 5);

            move_uploaded_file($file['tmp_name'], $destinationPath . $filename);
            $image = $filename;
        }
    }
    $_POST['data']['name'] = strip_tags($_POST['data']['name']);
    $_POST['data']['image_url'] = strip_tags($_POST['data']['image_url']);
    $_POST['data']['image'] = $image;
    $_POST['data']['category'] = implode(',', $_POST['data']['category']);
    $_POST['data']['semantic'] = implode(',', $_POST['data']['semantic']);
    $_POST['data']['alters'] = implode(',', $_POST['data']['alters']);
    $_POST['data']['lauttreu'] = isset($_POST['lauttreu']) ? 1 : 0;
    if ($_POST['data']['id'] != '') { 
		if($image=='')/*in case no new image is chosen*/
        {
            unset($_POST['data']['image']);
        }
        update_record('words', $_POST['data']['id'], $_POST['data']);
    } else {
        add_record('words', $_POST['data']);
    }
    header("Location:add_word.php");
}
$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$words = get_words();
?>
<!DOCTYPE html>
<html lang="de">
    <?php include './header.php'; ?>
    <body>
        <div class="wrapper">

            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                    <div class="row">
                        <div class="col-md-12">
                            <!-- begin panel -->
                            <div class="panel panel-inverse" >
                                <div class="panel-heading">
                                    <h4 class="panel-title">Wort hinzufügen</h4>
                                </div>
                                <div class="panel-body col-md-12">
                                    <div class="col-md-8">
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td>Wort</td>
                                                        <td>
                                                            <input type="text" name="data[name]" class="form-control" value="<?php echo @$word['name']; ?>">
                                                            <input type="hidden" name="data[id]" class="form-control" value="<?php echo @$word['id']; ?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Wortart</td>
                                                        <td>
                                                            <select class="form-control select2"  name="data[category][]"  >
                                                                <?php
                                                                foreach ($categories as $key => $value) {
                                                                    ?>
                                                                    <option <?php echo in_array($value['id'], @explode(',', @$word['category'])) ? 'selected' : ''; ?> value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Themengebiet</td>
                                                        <td>
                                                            <select class="form-control select2"  name="data[semantic][]"  multiple="multiple">
                                                                <?php
                                                                foreach ($semantic as $key => $value) {
                                                                    ?>
                                                                    <option <?php echo in_array($value['id'], @explode(',', @$word['semantic'])) ? 'selected' : ''; ?>  value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Alter</td>
                                                        <td>
                                                            <select class="form-control select2"  name="data[alters][]" >
                                                                <?php
                                                                foreach ($alters as $key => $value) {
                                                                    ?>
                                                                    <option <?php echo in_array($value['id'], @explode(',', @$word['alters'])) ? 'selected' : ''; ?>  value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Bild</td>
                                                        <td>
                                                            <input type="file" class="form-control" name="image" style="padding:0px">
                                                            <br>oder<br>
                                                            <input type="text" class="form-control" name="data[image_url]" style="padding:0px" value="<?php echo @$word['image_url']; ?>">
                                                            
                                                            <?php
                                                            if (isset($word['image']) && $word['image'] != '') {
                                                                echo '<img src="images/' . $word['image'] . '" style="width:200px;">';
                                                            }
                                                            else if (isset($word['image_url']) && $word['image_url'] != '') {
                                                                echo '<img src="' . $word['image_url'] . '" style="width:200px;">';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Meta</td>
                                                        <td>
                                                            <?php
                                                            if ($word['lauttreu'] == 1) {
                                                                echo '<label><input id="lauttreu" name="lauttreu" type="checkbox" value="1" checked>&nbsp Lauttreu</label>';
                                                            }
                                                            else {
                                                                echo '<label><input id="lauttreu" name="lauttreu" type="checkbox" value="1">&nbsp Lauttreu</label>';
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <input type="submit"  class="form-control btn btn-primary" value="Speichern">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </form>
                                    </div>
                                    <div class="col-md-12">
                                        <table id="data-table1" class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th colspan="7">Wörter</th>
                                                </tr>
                                                <tr>

                                                    <th>Name</th>
                                                    <th>Wortart</th>
                                                    <th>Themengebiet</th>
                                                    <th>Alter</th>
                                                    <th>Bild</th>
                                                    <th>Bild2</th>
                                                    <th>Bearbeiten / Löschen</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                    foreach ($words as $key => $value) {
                                                ?>
                                                    <tr>
                                                        <td><?php echo $value['name']; ?></td>
                                                        <td><?php echo implode(',', array_unique(explode(',', $value['category']))); ?></td>
                                                        <td><?php echo implode(',', array_unique(explode(',', $value['semantic']))); ?></td>
                                                        <td><?php echo implode(',', array_unique(explode(',', $value['alters']))); ?></td>
                                                        <td>
                                                            <?php
                                                            if (isset($value['image']) && $value['image'] != '') {
                                                                echo '<img src="images/' . $value['image'] . '" style="width:70px;">';
                                                            }
                                                            ?> 
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if (isset($value['image_url']) && $value['image_url'] != '') {
                                                                  echo '<img src="' . $value['image_url'] . '" style="width:70px;">';
                                                            }
                                                            ?> 
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-warning" href="add_word.php?action=edit&id=<?php echo $value['id']; ?>">Bearbeiten</a>
                                                            <a class="btn btn-danger" onclick="return confirm('Sind Sie sicher?');" href="add_word.php?action=delete&id=<?php echo $value['id']; ?>">Löschen</a>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!--row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->

        <!-- jQuery -->
        <script>

            $(document).ready(function (){
                $('#data-table1').DataTable( {
                    stateSave: true,
					"language": {
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					}
				});
                $('.select2').select2();
            });

        </script>
    </body>
</html>