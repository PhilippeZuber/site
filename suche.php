<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:index.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');


$categories = get_records('category');
$sementic = get_records('sementic');
$alters = get_records('alters');
$page = 'search';
?>
<html !DOCTYPE>
    <?php include './header.php'; ?><!--CSS+JS files/modal window/ sideba collapse-->
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                    <div class="row">
                        <div class="col-md-12">
                            <label><input id="search_image" checked type="checkbox" value="biler">&nbsp Mit Bildern</label>
                            <h2>Suchen</h2>
                            <div class="input-group">
                                <input type="text" id="search_text" class="form-control" placeholder="Suche nach...">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" onclick="search();">Los!</button>
                                </span>
                            </div>
                            <p>Suche mithilfe von <strong>*</strong> z.B. nach *le</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 ">
                            <div class="card">
                                <div class="card-header" id="headingOne">
                                    <h4 data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> Wortarten
                                    </h4>
                                </div>
                                <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                                    <div class="card-body">
                                        <table width="100%" border="0">
                                            <tbody>
                                                <?php
                                                foreach ($categories as $key => $value) {
                                                    if ($key % 2 == 0) {
                                                        echo '<tr>';
                                                    }
                                                    ?>
                                                <td><label><input type="checkbox" name="category[]" value="<?php echo $value['id']; ?>">&nbsp <?php echo $value['name']; ?></label></td>

                                                <?php
                                                if ($key + 1 % 2 == 0) {
                                                    echo '</tr>';
                                                }
                                            }
                                            ?>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--                        </div>
                                                    <div class="col-md-4">-->
                            <div class="card">
                                <div class="card-header" id="headingTwo">
                                    <h4 data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                        <span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span>Alter
                                    </h4>
                                </div>
                                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordion">
                                    <div class="card-body">
                                        <table width="100%" border="0">
                                            <tbody>
                                                <?php
                                                foreach ($alters as $key => $value) {
                                                    if ($key % 2 == 0) {
                                                        echo '<tr>';
                                                    }
                                                    ?>
                                                <td><label><input type="checkbox" name="alter[]"  value="<?php echo $value['id']; ?>">&nbsp <?php echo $value['name']; ?></label></td>

                                                <?php
                                                if ($key + 1 % 2 == 0) {
                                                    echo '</tr>';
                                                }
                                            }
                                            ?>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!--                        </div>
                                                    <div class="col-md-4">-->
                            <div class="card">
                                <div class="card-header" id="headingThree">
                                    <h4 data-toggle="collapse" data-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
                                        <span class="glyphicon glyphicon-menu-hamburger" aria-hidden="true"></span> Semantische Felder
                                    </h4>
                                </div>
                                <div id="collapseThree" class="collapse show" aria-labelledby="headingThree" data-parent="#accordion">
                                    <div class="card-body">
                                        <table width="100%" border="0">
                                            <tbody>
                                                <?php
                                                foreach ($sementic as $key => $value) {
//                                                    if ($key % 2 == 0) {
                                                    echo '<tr>';
//                                                    }
                                                    ?>
                                                <td><label><input type="checkbox" name="sementic[]"  value="<?php echo $value['id']; ?>">&nbsp <?php echo $value['name']; ?></label></td>

                                                <?php
//                                                if ($key + 1 % 2 == 0) {
                                                echo '</tr>';
//                                                }
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div><!--card-->
                        </div><!--column-->

                        <div class="col-md-8">
                            <table  class="table table-responsive table-striped" id="data-table1">
                                <thead>
                                    <tr>
                                        <th>Word</th>
                                        <th class="search_image_column">Image</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div><!--row-->
                </div><!--/container-->		
            </div><!--/content-->
        </div><!--/wrapper-->

        <!-- jQuery -->
        <script>
            var table;
            $(document).ready(function (){
				$('#example').dataTable({
					"oLanguage": {
				  		"sProcessing": "Einen Moment..."
					}
				});
            });
			

            function search() {
                var category = [];
                var sementic = [];
                var alter = [];
                $.each($("input[name='category[]']:checked"), function () {
                    category.push($(this).val());
                });
                $.each($("input[name='sementic[]']:checked"), function () {
                    sementic.push($(this).val());
                });
                $.each($("input[name='alter[]']:checked"), function () {
                    alter.push($(this).val());
                });
                
                $('#data-table1').DataTable().clear().destroy();

                table = $('#data-table1').DataTable({
                    ajax: {
                        url: 'search_word.php',
                        data: {
                            search_image: $('#search_image').prop('checked'),
                            search_text: $('#search_text').val(),
                            category: category,
                            sementic: sementic,
                            alter: alter,

                        }
                    },
                    searching: false,
                    "processing": true,
                    "serverSide": true,
                });
            }
        </script>
    </body>
</html>