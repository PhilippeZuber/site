<?php
session_start();
if (isset($_SESSION['id'])) {
    header("Location:search.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');


$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$page = 'index';
?>
<!DOCTYPE html>
 <html lang="de">
    <?php include './header.php'; ?><!--CSS+JS files/modal window User settings & Register, Login/ sidebar collapse-->
	<?php include './SearchInfModal.php'; ?><!--Modal window Search Information used in index.php / search.php only-->
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning" role="alert">Wortlab befindet sich zurzeit in der Aufbauphase. Die Datenbank wird ständig erweitert und neue Filterkriterien werden hinzugefügt. Rückmeldungen und Ideen nehmen wir gerne unter <a href="mailto:info@wortlab.ch">info@wortlab.ch</a> entgegen.</div>
                            <h1>Suchen</h1>
                            <div class="input-group">
                                <input type="text" id="search_text" class="form-control" placeholder="Suche nach...">
                                <span class="input-group-btn">
                                    <button id="searchbtn" class="btn btn-primary" type="button" onClick="search();">Los!</button>
                                </span>
                            </div>
							<p>Suche mithilfe von <strong>*</strong> z.B. nach *le  um Wörter zu finden die auf le Enden.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <label><input id="search_image" checked type="checkbox" value="biler">&nbsp; Mit Bildern</label>
                        </div>
                        <div class="col-md-2">
                            <label><input id="lauttreu" disabled type="checkbox" value="1">&nbsp; Lauttreu</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-link btn-lg" data-toggle="modal" data-target="#SearchInfModal" style="margin-bottom: 20px">
								Weitere Infos zum Suchen
							</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 ">
                            <details open>
                                <summary><span class="glyphicon glyphicon-plus-sign"></span> Wortarten</summary>
                                <table width="100%" border="0">
                                    <tbody>
                                        <?php
                                        foreach ($categories as $key => $value) {
                                            if ($key % 2 == 0) {
                                                echo '<tr>';
                                            }
                                            ?>
                                            <td><label><input type="checkbox" name="category[]" value="<?php echo $value['id']; ?>">&nbsp; <?php echo $value['name']; ?></label></td>

                                            <?php
                                            if ($key + 1 % 2 == 0) {
                                                echo '</tr>';
                                            }
                                        }
                                            ?>
                                    </tbody>
                                </table>
                            </details>
                                <details open>
                                    <summary><span class="glyphicon glyphicon-plus-sign"></span> Alter</summary>
                                    <table width="100%" border="0">
                                        <tbody>
                                            <?php
                                            foreach ($alters as $key => $value) {
                                                if ($key % 2 == 0) {
                                                    echo '<tr>';
                                                }
                                            ?>
                                                <td><label><input type="checkbox" name="alter[]"  value="<?php echo $value['id']; ?>">&nbsp; <?php echo $value['name']; ?></label></td>

                                                <?php
                                                if ($key + 1 % 2 == 0) {
                                                    echo '</tr>';
                                                }
                                            }
                                                ?>
                                        </tbody>
                                    </table>
                                </details>
                                <details open>
                                    <summary><span class="glyphicon glyphicon-plus-sign"></span> Kategorien</summary>
                                    <input class="form-control" id="semanticInput" type="text" placeholder="Suchen...">        
                                    <table width="100%" border="0">
                                        <tbody id="semanticTable">
                                            <?php
                                            foreach ($semantic as $key => $value) {
//                                                    if ($key % 2 == 0) {
                                                echo '<tr>';
//                                                    }
                                                ?>
                                            <td><label><input type="checkbox" name="semantic[]"  value="<?php echo $value['id']; ?>">&nbsp; <?php echo $value['name']; ?></label></td>

                                            <?php
//                                                if ($key + 1 % 2 == 0) {
                                            echo '</tr>';
//                                                }
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </details>
                        </div><!--column-->

                        <div class="col-md-8" id="datatables">
                            <table  class="table table-responsive table-striped" id="data-table1">
                                <thead>
                                    <tr>
                                        <th>Wort</th>
                                        <th class="search_image_column">Bild</th>
                                        <th class="search_image_column">Bild2</th>
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
			/*Enter key possible for search function trigger onclick*/
			var input = document.getElementById("search_text");
				input.addEventListener("keyup", function(event) {
					event.preventDefault();
					if (event.keyCode === 13) {
						document.getElementById("searchbtn").click();
					}
				});
			/*Initialising data-table*/
            var table;
            $(document).ready(function (){
				$('#data-table1').DataTable( {
					"language": {
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					},
					searching: false,
					paging: false,
				});
            });
			
			/*Search function*/
            function search() {
                var category = [];
                var semantic = [];
                var alter = [];
                $.each($("input[name='category[]']:checked"), function () {
                    category.push($(this).val());
                });
                $.each($("input[name='semantic[]']:checked"), function () {
                    semantic.push($(this).val());
                });
                $.each($("input[name='alter[]']:checked"), function () {
                    alter.push($(this).val());
                });
                
                $('#data-table1').DataTable().clear().destroy();

                table = $('#data-table1').DataTable({
					"language": {/*data-table in german*/
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					},
                    ajax: {/*giving values*/
                        url: 'search_word.php',
                        data: {
                            search_image: $('#search_image').prop('checked'),
                            search_text: $('#search_text').val(),
                            category: category,
                            semantic: semantic,
                            alter: alter,
                            lauttreu: $('#lauttreu').prop('checked'),
                        }
                    },
					dom: 'Bfrtip',/*Position of Buttons*/
					buttons: [
						/*{
							extend: 'selectAll',
							text: 'Zeilen wählen'
						},*/
						{
								extend: 'excel',
								text: 'Für Excel Speichern',
								exportOptions: {
									modifier: {
										page: 'current'
									}
								}
						},
						/*{
								extend: 'pdf',
								text: 'Als pdf Speichern',
								exportOptions: {
									modifier: {
										page: 'current'
									}
								}
						},*/
						{
								extend: 'print',
								text: 'Drucken / Als pdf Speichern',
								/*message: "(C) by ...",*/
								exportOptions: {
									modifier: {
										page: 'current'
									},
									stripHtml: false
								}
						},
						{
								extend: 'copy',
								text: 'Zwischenablage',
								exportOptions: {
									modifier: {
										page: 'current'
									}
								}
						}
					],
					select: {
						style: 'multi'
					},
                    searching: false,
                    "processing": true,
                    "serverSide": true,
                });
            }
			/*var table = $('#data-table1').DataTable();
 			var data = table.buttons.exportData( {
				columns: ':visible'
			} );*/
        </script>
    </body>
</html>