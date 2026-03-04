<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location:login.php");
} else {
    $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');


$categories = get_records('category');
$semantic = get_records('semantic');
$alters = get_records('alters');
$page = 'search';
?>
<html !DOCTYPE>
    <?php include './header.php'; ?><!--CSS+JS files/modal window/ sidebar collapse-->
	<?php include './SearchInfModal.php'; ?><!--Modal window used in search.php only-->
    <body>
        <div class="wrapper">
            <?php include 'sidebar.php'; ?>
            <!-- Page Content Holder -->
            <div id="content">
                <?php include './navigation.php'; ?>
                <div class=container-fluid>
                    <div class="row">
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
                            <label><input id="search_image" checked type="checkbox" value="biler">&nbsp; Mit Bildern</label>
                        </div>
                        <div class="col-md-2">
                            <label><input id="lauttreu" type="checkbox" value="1">&nbsp; Lauttreu</label>
                        </div>
                        <div class="col-md-4">
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
                                <summary><span class="glyphicon glyphicon-random"></span> Minimalpaar-Finder</summary>
                                <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <label><input id="minimalpair_enabled" type="checkbox" value="1">&nbsp; Minimalpaar-Finder aktivieren</label>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-sm-4">
                                            <label for="minimalpair_from">Von</label>
                                            <input id="minimalpair_from" type="text" class="form-control" maxlength="1" placeholder="z.B. G">
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="minimalpair_to">Nach</label>
                                            <input id="minimalpair_to" type="text" class="form-control" maxlength="1" placeholder="z.B. D">
                                        </div>
                                        <div class="col-sm-4" style="margin-top: 24px;">
                                            <button id="minimalpair_find" class="btn btn-primary btn-sm">Paare finden</button>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 8px;">
                                        <div class="col-sm-12 text-muted" style="font-size: 12px;">
                                            Strenger Modus: gleiche Wortlänge, genau ein Buchstabenwechsel von → nach.
                                        </div>
                                    </div>
                                </div>
                            </details>
                            <details class="action-accordion search-accordion-block">
                                <summary><span class="glyphicon glyphicon-folder-open"></span> Wortsammlungen</summary>
                                <div style="margin-top: 15px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="collection_select">Sammlung</label>
                                            <select id="collection_select" class="form-control">
                                                <option value="">Bitte wählen...</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-6" style="margin-top: 24px;">
                                            <button id="collection_load" class="btn btn-primary btn-sm">Auswahl laden</button>
                                            <button id="collection_save" class="btn btn-success btn-sm">Auswahl speichern</button>
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top: 10px;">
                                        <div class="col-sm-12">
                                            <button id="collection_new" class="btn btn-default btn-sm">Neu</button>
                                            <button id="collection_rename" class="btn btn-default btn-sm">Umbenennen</button>
                                            <button id="collection_delete" class="btn btn-danger btn-sm">Löschen</button>
                                        </div>
                                    </div>
                                </div>
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
                            <!-- Active Filters Display -->
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
                                        <th class="minimalpair-column">Minimalpaar</th>
                                        <th class="minimalpair-column">Unterschied</th>
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

            ['minimalpair_from', 'minimalpair_to'].forEach(function(id) {
                var element = document.getElementById(id);
                element.addEventListener("keyup", function(event) {
                    event.preventDefault();
                    if (event.keyCode === 13) {
                        document.getElementById("minimalpair_find").click();
                    }
                });
            });
			/*Initialising data-table*/
            var table;
            var memorySelectedIds = {};
            var collectionsById = {};

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

            // ===== STICKY FILTER TRACKING =====
            var activeFilters = {};

            // Map für Filter-Labels (ID/name => Display-Name)
            var filterLabels = {
                'search_image': 'Mit Bildern',
                'lauttreu': 'Lauttreu',
                'minimalpair_enabled': 'Minimalpaar-Finder aktiv'
            };

            // Populate category/semantic/alter labels on page load
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

            // Update active filters display
            function updateActiveFilters() {
                var container = $('#active-filters-container');
                var tagsList = $('#active-filters');
                tagsList.empty();

                var hasFilters = false;

                // Add search text filter
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

                // Add not_letter filter
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

                var minimalPairEnabled = $('#minimalpair_enabled').is(':checked');
                var minimalPairFrom = $.trim($('#minimalpair_from').val());
                var minimalPairTo = $.trim($('#minimalpair_to').val());
                if (minimalPairEnabled && minimalPairFrom && minimalPairTo && minimalPairFrom !== minimalPairTo) {
                    hasFilters = true;
                    tagsList.append(
                        '<span class="filter-tag">' +
                        'Unterschied: &quot;' + escapeHtml(minimalPairFrom) + '→' + escapeHtml(minimalPairTo) + '&quot;' +
                        '<button type="button" class="filter-tag-remove" data-filter-type="minimalpair_diff" title="Entfernen">×</button>' +
                        '</span>'
                    );
                }

                // Add checkbox filters
                $('input[type="checkbox"]').each(function() {
                    if ($(this).is(':checked')) {
                        hasFilters = true;
                        var value = $(this).val();
                        var name = $(this).attr('name');
                        var id = $(this).attr('id');

                        var label = '';
                        if (id === 'search_image' || id === 'lauttreu' || id === 'minimalpair_enabled') {
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

                // Show/hide container
                if (hasFilters) {
                    container.addClass('has-filters');
                } else {
                    container.removeClass('has-filters');
                }
            }

            // Helper function to escape HTML
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

            // Remove filter on tag click
            $(document).on('click', '.filter-tag-remove', function(e) {
                e.preventDefault();
                var filterType = $(this).data('filter-type');
                var filterId = $(this).data('filter-id');
                var filterName = $(this).data('filter-name');

                if (filterType === 'search_text') {
                    $('#search_text').val('');
                } else if (filterType === 'not_letter') {
                    $('#not_letter').val('');
                } else if (filterType === 'minimalpair_diff') {
                    $('#minimalpair_from').val('');
                    $('#minimalpair_to').val('');
                } else if (filterId) {
                    $('#' + filterId).prop('checked', false);
                } else if (filterName) {
                    $('input[name="' + filterName + '"][value="' + $(this).data('filter-value') + '"]').prop('checked', false);
                }

                updateActiveFilters();
                search();
            });

            // Clear all filters
            $('#clear-all-filters').on('click', function(e) {
                e.preventDefault();
                $('#search_text').val('');
                $('#not_letter').val('');
                $('#minimalpair_from').val('');
                $('#minimalpair_to').val('');
                $('input[type="checkbox"]').prop('checked', false);
                updateActiveFilters();
                search();
            });

            function shouldShowMinimalPairColumns() {
                var from = $.trim($('#minimalpair_from').val());
                var to = $.trim($('#minimalpair_to').val());
                return $('#minimalpair_enabled').prop('checked') && from !== '' && to !== '' && from !== to;
            }

            $(document).ready(function (){
				var showMinimalPairColumns = shouldShowMinimalPairColumns();
				$('#data-table1').DataTable( {
					"language": {
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					},
					searching: false,
					paging: false,
                    order: [[1, 'asc']],
                    columnDefs: [
                        { targets: 0, orderable: false, searchable: false, width: '80px' },
                        { targets: [4,5], orderable: false },
                        { targets: [2,3], visible: showMinimalPairColumns }
                    ]
				});

                // Initialize filter labels and display
                initFilterLabels();
                updateActiveFilters();

                // Event listeners for filter changes
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

                $('#minimalpair_from, #minimalpair_to').on('change', function() {
                    updateActiveFilters();
                });

                $('#minimalpair_find').on('click', function(e) {
                    e.preventDefault();
                    updateActiveFilters();
                    search();
                });

                loadCollections();
            });

            function loadCollections(selectedId) {
                $.post('word_collections.php', { action: 'list' }, function (response) {
                    var select = $('#collection_select');
                    collectionsById = {};
                    select.empty();
                    select.append('<option value="">Bitte wählen...</option>');
                    if (response && response.collections) {
                        $.each(response.collections, function (index, item) {
                            collectionsById[item.id] = item;
                            select.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    }
                    if (selectedId) {
                        select.val(String(selectedId));
                    }
                }, 'json');
            }
			
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

                var minimalpairFrom = $.trim($('#minimalpair_from').val());
                var minimalpairTo = $.trim($('#minimalpair_to').val());
                var minimalpairEnabled = $('#minimalpair_enabled').prop('checked') && minimalpairFrom !== '' && minimalpairTo !== '' && minimalpairFrom !== minimalpairTo;
                
                $('#data-table1').DataTable().clear().destroy();

                table = $('#data-table1').DataTable({
					"language": {/*data-table in german*/
						"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/German.json"
					},
                    ajax: {/*giving values*/
                        url: 'search_word.php',
                        data: {
                            include_selection: true,
                            search_image: $('#search_image').prop('checked'),
                            search_text: $('#search_text').val(),
                            not_letter: $('#not_letter').val(),
                            category: category,
                            semantic: semantic,
                            alter: alter,
                            lauttreu: $('#lauttreu').prop('checked'),
                            minimalpair_enabled: minimalpairEnabled,
                            minimalpair_from: minimalpairFrom,
                            minimalpair_to: minimalpairTo,
                        }
                    },
					dom: 'Blfrtip',/*Position of Buttons*/
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
                        { targets: [4,5], orderable: false },
                        { targets: [2,3], visible: minimalpairEnabled }
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

            $('#collection_new').on('click', function () {
                var name = prompt('Name der neuen Sammlung:');
                if (!name) {
                    return;
                }
                var ids = Object.keys(memorySelectedIds).join(',');
                $.post('word_collections.php', { action: 'create', name: name, word_ids: ids }, function (response) {
                    if (response && response.id) {
                        loadCollections(response.id);
                    }
                }, 'json');
            });

            $('#collection_save').on('click', function () {
                var selectedId = $('#collection_select').val();
                if (!selectedId) {
                    alert('Bitte zuerst eine Sammlung auswählen.');
                    return;
                }
                var name = $('#collection_select option:selected').text();
                var ids = Object.keys(memorySelectedIds).join(',');
                $.post('word_collections.php', { action: 'update', id: selectedId, name: name, word_ids: ids }, function () {
                    loadCollections(selectedId);
                }, 'json');
            });

            $('#collection_load').on('click', function () {
                var selectedId = $('#collection_select').val();
                if (!selectedId) {
                    alert('Bitte zuerst eine Sammlung auswählen.');
                    return;
                }
                $.post('word_collections.php', { action: 'get', id: selectedId }, function (response) {
                    if (response && response.collection) {
                        var ids = response.collection.word_ids ? response.collection.word_ids.split(',') : [];
                        memorySelectedIds = {};
                        $.each(ids, function (index, value) {
                            if (value !== '') {
                                memorySelectedIds[value] = true;
                            }
                        });
                        updateMemorySelectedCount();
                        applySelectionToTable();
                        search();
                    }
                }, 'json');
            });

            $('#collection_rename').on('click', function () {
                var selectedId = $('#collection_select').val();
                if (!selectedId) {
                    alert('Bitte zuerst eine Sammlung auswählen.');
                    return;
                }
                var currentName = $('#collection_select option:selected').text();
                var newName = prompt('Neuer Name der Sammlung:', currentName);
                if (!newName) {
                    return;
                }
                var currentCollection = collectionsById[selectedId];
                var wordIds = currentCollection ? currentCollection.word_ids : '';
                $.post('word_collections.php', { action: 'update', id: selectedId, name: newName, word_ids: wordIds }, function () {
                    loadCollections(selectedId);
                }, 'json');
            });

            $('#collection_delete').on('click', function () {
                var selectedId = $('#collection_select').val();
                if (!selectedId) {
                    alert('Bitte zuerst eine Sammlung auswählen.');
                    return;
                }
                var currentName = $('#collection_select option:selected').text();
                if (!confirm('Sammlung "' + currentName + '" wirklich löschen?')) {
                    return;
                }
                $.post('word_collections.php', { action: 'delete', id: selectedId }, function () {
                    loadCollections();
                }, 'json');
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
        <?php include 'footer.php'; ?>
    </body>
</html>