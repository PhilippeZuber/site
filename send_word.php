<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

$page = 'send_word';

if (isset($_POST['data'])) {

    $_POST['data']['name'] = strip_tags($_POST['data']['name']);
    $_POST['data']['category'] = implode(',', $_POST['data']['category']);
    $_POST['data']['semantic'] = implode(',', $_POST['data']['semantic']);
    $_POST['data']['alters'] = implode(',', $_POST['data']['alters']);
    $_POST['data']['lauttreu'] = isset($_POST['lauttreu']) ? 1 : 0;

    add_record('pending', $_POST['data']);
    header("Location:send_word.php");
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
                                    <div class="panel-heading-btn">
                                    </div>
                                    <h4 class="panel-title">Wort einsenden</h4>
                                </div>
                                <div class="panel-body col-md-12">
                                    <div class="col-md-8">
                                        <form method="POST" action="" enctype="multipart/form-data">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td>Wort</td>
                                                        <td>
                                                            <input type="text" id="word" name="data[name]" class="form-control" value="<?php echo @$word['name']; ?>">
                                                        </td>
                                                    </tr> 
                                                    <tr>
                                                        <td>Wortart</td>
                                                        <td>
                                                            <select id="category" name="data[category][]" class="form-control select2">
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
                                                            <select id="semantic" name="data[semantic][]" class="form-control select2" multiple="multiple">
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
                                                            <select id="alters" name="data[alters][]" class="form-control select2">
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
                                                    <td>Meta</td>
                                                        <td>
                                                            <label><input id="lauttreu" name="lauttreu" type="checkbox" value="1">&nbsp Lauttreu</label>
                                                        </td>
                                                    <tr>
                                                        <td></td>
                                                        <td>
                                                            <input type="submit"  class="form-control btn btn-primary" value="Senden" id="senden">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </form>
										<div style="display:none;" id="thankyou_message">
											<h2><em>Danke!</em><br> Wir werden das Wort überprüfen.</h2>
										</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->
        <!-- jQuery -->
        <script>
            $(document).ready(function (){
                $('#data-table1').DataTable( {
					"language": {
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					}
				});
                $('.select2').select2();
                $("#senden").click(function(){
                    $("#thankyou_message").css("display", "block");
                });
            });
        </script>
    </body>
</html>