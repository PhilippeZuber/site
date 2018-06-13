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


$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$words = get_words();
?>
<!doctype html>
<html>
    <?php include './header.php'; ?>
    <body>
        <div class="wrapper">

            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>

                <div class=container-fluid>
                    <div class="row">
                        <!-- begin col-6 -->
                        <div class="col-md-12">
                            <!-- begin panel -->
                            <div class="panel panel-inverse" >
                                <div class="panel-heading">
                                    <div class="panel-heading-btn">
                                    </div>
                                    <h4 class="panel-title">Wort einsenden</h4>
                                </div>
                                <div class="panel-body col-md-12">
                                    <div class="col-md-6">
                                        <form method="POST" action="mailto:kontakt@zubermedien.ch?subject=Neues Wort" enctype="text/plain">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td>Wort</td>
                                                        <td>
                                                            <input type="text" name="name" class="form-control" value="<?php echo @$word['name']; ?>">
                                                        </td>
                                                    </tr> 
                                                    <tr>
                                                        <td>Wortart</td>
                                                        <td>
                                                            <select class="form-control select2"  name="category"  >
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
                                                            <select class="form-control select2"  name="semantic"  multiple="multiple">
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
                                                            <select class="form-control select2"  name="alters" >
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
                                                        <td></td>
                                                        <td>
                                                            <input type="submit"  class="form-control btn btn-primary" value="Senden">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </form>
										<p>Nachdem Sie Senden gewählt haben, wird das Wort mit ihrem Mail-Programm ans uns verschickt. Sie können Bildvorschläge als Anhang hinzufügen.</p>
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
            });
        </script>
    </body>
</html>