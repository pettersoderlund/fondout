'use strict';
$(document).ready(function() {
  $('#filter-sustainability').selectize({
      plugins: ['remove_button'],
      sortField: 'text'
  });

  $('[data-toggle=popover]').popover({
    html: true,
    trigger: 'hover'
  });
});
