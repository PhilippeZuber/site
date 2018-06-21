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
	<script data-cfasync="false" type="text/javascript" src="js/form-submission-handler.js"></script><!--Local because Github version may change-->
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
                                        <form method="POST" action="https://script.google.com/macros/s/AKfycbxoIsicvEjPzWlkuDKTxXaH7oj8BfXPinwJpWtN/exec" id="gform" enctype="multipart/form-data">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td>Wort</td>
                                                        <td>
                                                            <input type="text" id="word" name="word" class="form-control" value="<?php echo @$word['name']; ?>">
                                                        </td>
                                                    </tr> 
                                                    <tr>
                                                        <td>Wortart</td>
                                                        <td>
                                                            <select id="category" name="category" class="form-control select2">
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
                                                            <select id="semantic" name="semantic" class="form-control select2" multiple="multiple">
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
                                                            <select id="alters" name="alters" class="form-control select2">
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
                                                        <td>Ihre E-Mail Adresse</td>
                                                        <td>
                                                            <input type="email" id="email" name="email" class="form-control" value="" required placeholder="Gültige Adresse"><span id="email-invalid" style="display:none">Es muss eine gültige Adresse sein.</span>
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
										<div style="display:none;" id="thankyou_message">
											<h2><em>Danke!</em> Wir werden das Wort überprüfen.</h2>
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
            });
        </script>
    </body>
</html>