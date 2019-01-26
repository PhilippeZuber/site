/********* Function to filter semantic table / Kategorien Filter*****************/
$(document).ready(function(){
  $("#semanticInput").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#semanticTable tr").filter(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
  });
});