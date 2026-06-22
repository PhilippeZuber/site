<?php
session_start();
$cookie = isset($_COOKIE['wortlab_remember']) ? $_COOKIE['wortlab_remember'] : '';
$cookie2 = isset($_COOKIE['wortlab_role']) ? $_COOKIE['wortlab_role'] : '';
if ($cookie) {
    $_SESSION['id'] = $cookie;
    $_SESSION['role'] = $cookie2;
}

$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

require_once('system/data.php');
require_once('system/security.php');


$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$page = 'index';
$page_title     = 'Wörter suchen für Logopädie und Schule';
$page_meta_desc = 'Wortlab: Wörter nach Anfangsbuchstabe, Thema, Wortart und Alter suchen. Die Spezialdatenbank für Logopädinnen, Lehrpersonen und Therapeutinnen in der Schweiz.';
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
                        <!--<div class="col-md-8">
                            <div class="media">
                                <div class="media-left media-middle">
                                    <a href="https://www.wortlab.ch/login.php">
                                    <img class="media-object" src="img\braendi_picto.jpg" alt="Brändi Picto">
                                    </a>
                                </div>
                                <div class="media-body">
                                    <h4 class="media-heading">Spiel Gewinnen</h4>
                                    <p>Alle registrierten Benutzerinnen von Wortlab nehmen automatisch an der Verlosung der Spiele Brändi Picto™ und Brändi Picto Plus™ teil.</p>
                                    <p>Die Gewinnerin wird am 28. Februar ausgelost.</p>
                                    <p>Du bist noch nicht registriert? Dann registriere dich jetzt!</p>
                                    <p><a href="https://www.wortlab.ch/login.php" class="btn btn-primary" role="button">Registrieren</a></p>
                                </div>
                            </div>
                        </div>-->
                        <!--<div class="col-md-12">
                            <div class="alert alert-warning" role="alert">Wortlab befindet sich zurzeit in der Aufbauphase. Die Datenbank wird ständig erweitert und neue Filterkriterien werden hinzugefügt. Rückmeldungen und Ideen nehmen wir gerne unter <a href="mailto:info@wortlab.ch">info@wortlab.ch</a> entgegen.</div>
                        </div>-->
                        <div class="col-md-12">
                            <h1>Suchen <a data-toggle="modal" data-target="#SearchInfModal" class="badge"><span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span></a></h1>
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
                            <label><input id="lauttreu" type="checkbox" value="1">&nbsp; Lauttreu</label>
                        </div>
                        <div class="col-md-2">
                            <label><input id="image_mode_ausmalbild" type="checkbox" value="1">&nbsp; Ausmalbild</label>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" id="not_letter" placeholder="Buchstabe ausschliessen" aria-describedby="Buchstabe ausschliessen">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 filters-container">
                            <details class="search-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-filter"></span> Wortarten</summary>
                                <table style="margin-top: 20px;" width="100%" border="0">
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
                            <details class="search-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-filter"></span> Alter</summary>
                                <table style="margin-top: 20px;" width="100%" border="0">
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
                            <details class="search-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-filter"></span> Kategorien</summary>
                                <input style="margin-top: 20px;" class="form-control" id="semanticInput" type="text" placeholder="Suchen...">        
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
                            <details class="action-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-play-circle"></span> Memory erstellen &ndash; <span id="memory-selected-count">0</span> Wörter ausgewählt</summary>
                                <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label for="memory_mode">Modus</label>
                                            <select id="memory_mode" class="form-control">
                                                <option value="image">Bilder</option>
                                                <option value="text">Text</option>
                                                <option value="mixed">Gemischt</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="memory_pairs">Max. Paare (optional)</label>
                                            <input id="memory_pairs" type="number" min="2" class="form-control" placeholder="z.B. 8">
                                        </div>
                                        <div class="col-sm-4" style="margin-top: 24px;">
                                            <button id="memory_create" class="btn btn-success btn-sm">Memory starten</button>
                                        </div>
                                    </div>
                                </div>
                            </details>
                            <details class="action-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-print"></span> Arbeitsblatt erstellen &ndash; <span id="worksheet-selected-count">0</span> Wörter ausgewählt</summary>
                                <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="worksheet_layout">Layout</label>
                                            <select id="worksheet_layout" class="form-control">
                                                <option value="cards">Bildkarten (4 pro Seite)</option>
                                                <option value="list">Wortliste</option>
                                                <option value="memory">Memory-Karten</option>
                                                <option value="bingo">Bingo-Karte (3x3)</option>
                                                <option value="syllables">Silbenkarten</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6" style="margin-top: 24px;">
                                            <button id="worksheet_create" class="btn btn-primary btn-sm">
                                                <span class="glyphicon glyphicon-print"></span> Arbeitsblatt erstellen
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </details>
                            <div id="active-filters-container">
                                <label id="active-filters-label">Aktive Filter:</label>
                                <div id="active-filters"></div>
                                <button id="clear-all-filters" class="btn btn-sm btn-warning"><span class="glyphicon glyphicon-remove"></span> Alle Filter löschen</button>
                            </div>
                        </div><!--column-->

                        <div class="col-md-8" id="datatables">
                            <table  class="table table-responsive table-striped" id="data-table1">
                                <thead>
                                    <tr>
                                        <th>Auswahl</th>
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
            input.addEventListener("keyup", function (event) {
                event.preventDefault();
                if (event.keyCode === 13) {
                    document.getElementById("searchbtn").click();
                }
            });
            /*Initialising data-table*/
            var table;
            var memorySelectedIds = {};

            function applySelectionToTable() {
                $('#data-table1 .memory-select').each(function () {
                    var id = $(this).val();
                    $(this).prop('checked', !!memorySelectedIds[id]);
                });
            }

            function updateMemorySelectedCount() {
                var count = Object.keys(memorySelectedIds).length;
                $('#memory-selected-count').text(count);
                $('#worksheet-selected-count').text(count);
            }

            var activeFilters = {};

            var filterLabels = {
                'lauttreu': 'Lauttreu',
                'image_mode_ausmalbild': 'Ausmalbild'
            };

            function initFilterLabels() {
                $('input[name="category[]"]').each(function() {
                    var id = $(this).val();
                    if (!filterLabels['category_' + id]) {
                        filterLabels['category_' + id] = $(this).next('label').text() || $(this).parent('label').text();
                    }
                });
                $('input[name="alter[]"]').each(function() {
                    var id = $(this).val();
                    if (!filterLabels['alter_' + id]) {
                        filterLabels['alter_' + id] = $(this).next('label').text() || $(this).parent('label').text();
                    }
                });
                $('input[name="semantic[]"]').each(function() {
                    var id = $(this).val();
                    if (!filterLabels['semantic_' + id]) {
                        filterLabels['semantic_' + id] = $(this).next('label').text() || $(this).parent('label').text();
                    }
                });
            }

            function updateActiveFilters() {
                var container = $('#active-filters-container');
                var tagsList = $('#active-filters');
                tagsList.empty();

                var hasFilters = false;

                var searchText = $('#search_text').val();
                if (searchText) {
                    hasFilters = true;
                    tagsList.append(
                        '<span class="filter-tag">' +
                        'Suche: &quot;' + escapeHtml(searchText) + '&quot;' +
                        '<button type="button" class="filter-tag-remove" data-filter-type="search_text" title="Entfernen">×</button>' +
                        '</span>'
                    );
                }

                var notLetter = $('#not_letter').val();
                if (notLetter) {
                    hasFilters = true;
                    tagsList.append(
                        '<span class="filter-tag">' +
                        'Ausschluss: &quot;' + escapeHtml(notLetter) + '&quot;' +
                        '<button type="button" class="filter-tag-remove" data-filter-type="not_letter" title="Entfernen">×</button>' +
                        '</span>'
                    );
                }

                $('input[type="checkbox"]').each(function() {
                    if ($(this).is(':checked')) {
                        hasFilters = true;
                        var value = $(this).val();
                        var name = $(this).attr('name');
                        var id = $(this).attr('id');

                        var label = '';
                        if (id === 'lauttreu' || id === 'image_mode_ausmalbild') {
                            label = filterLabels[id];
                        } else if (name === 'category[]') {
                            label = filterLabels['category_' + value];
                        } else if (name === 'alter[]') {
                            label = filterLabels['alter_' + value];
                        } else if (name === 'semantic[]') {
                            label = filterLabels['semantic_' + value];
                        }

                        if (label) {
                            tagsList.append(
                                '<span class="filter-tag">' +
                                escapeHtml(label) +
                                '<button type="button" class="filter-tag-remove" data-filter-id="' + $(this).attr('id') + '" data-filter-name="' + name + '" data-filter-value="' + value + '" title="Entfernen">×</button>' +
                                '</span>'
                            );
                        }
                    }
                });

                if (hasFilters) {
                    container.addClass('has-filters');
                } else {
                    container.removeClass('has-filters');
                }
            }

            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            $(document).on('click', '.filter-tag-remove', function(e) {
                e.preventDefault();
                var filterType = $(this).data('filter-type');
                var filterId = $(this).data('filter-id');
                var filterName = $(this).data('filter-name');

                if (filterType === 'search_text') {
                    $('#search_text').val('');
                } else if (filterType === 'not_letter') {
                    $('#not_letter').val('');
                } else if (filterId) {
                    $('#' + filterId).prop('checked', false);
                } else if (filterName) {
                    $('input[name="' + filterName + '"][value="' + $(this).data('filter-value') + '"]').prop('checked', false);
                }

                updateActiveFilters();
                search();
            });

            $('#clear-all-filters').on('click', function(e) {
                e.preventDefault();
                $('#search_text').val('');
                $('#not_letter').val('');
                $('input[type="checkbox"]').prop('checked', false);
                updateActiveFilters();
                search();
            });

            $(document).ready(function () {
                $('#data-table1').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
                    },
                    searching: false,
                    paging: false,
                    order: [[1, 'asc']],
                    columnDefs: [
                        { targets: 0, orderable: false, searchable: false, width: '80px' },
                        { targets: [2,3], orderable: false }
                    ]
                });

                initFilterLabels();
                updateActiveFilters();

                $('input[type="checkbox"]').on('change', function() {
                    updateActiveFilters();
                    search();
                });

                $('#search_text').on('change', function() {
                    updateActiveFilters();
                });

                $('#not_letter').on('change', function() {
                    updateActiveFilters();
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
                            source_page: 'index.php',
                            include_selection: true,
                            search_image: true,
                            image_mode: $('#image_mode_ausmalbild').prop('checked') ? 'ausmalbild' : 'standard',
                            search_text: $('#search_text').val(),
                            not_letter: $('#not_letter').val(),
                            category: category,
                            semantic: semantic,
                            alter: alter,
                            lauttreu: $('#lauttreu').prop('checked'),
                        }
                    },
                    dom: 'Blfrtip', /*Position of Buttons*/
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
                    order: [[1, 'asc']],
                    columnDefs: [
                        { targets: 0, orderable: false, searchable: false, width: '80px' },
                        { targets: [2,3], orderable: false }
                    ],
                    drawCallback: function () {
                        applySelectionToTable();
                        updateMemorySelectedCount();
                    }
                });
            }

            $(document).on('change', '.memory-select', function () {
                var id = $(this).val();
                if ($(this).is(':checked')) {
                    memorySelectedIds[id] = true;
                } else {
                    delete memorySelectedIds[id];
                }
                updateMemorySelectedCount();
            });

            $('#memory_create').on('click', function () {
                var ids = Object.keys(memorySelectedIds);
                if (ids.length < 2) {
                    alert('Bitte mindestens 2 Wörter auswählen.');
                    return;
                }
                var mode = $('#memory_mode').val();
                var pairs = $('#memory_pairs').val();
                var url = 'memory.php?ids=' + encodeURIComponent(ids.join(',')) + '&mode=' + encodeURIComponent(mode);
                if (pairs) {
                    url += '&pairs=' + encodeURIComponent(pairs);
                }
                window.location.href = url;
            });

            $('#worksheet_create').on('click', function () {
                var ids = Object.keys(memorySelectedIds);
                if (ids.length < 1) {
                    alert('Bitte mindestens 1 Wort auswählen.');
                    return;
                }
                var layout = $('#worksheet_layout').val();
                var url = 'worksheet_generator.php?ids=' + encodeURIComponent(ids.join(',')) + '&layout=' + encodeURIComponent(layout);
                window.location.href = url;
            });
            /*var table = $('#data-table1').DataTable();
             var data = table.buttons.exportData( {
             columns: ':visible'
             } );*/
        </script>
    </body>
</html>