'use strict';

function isTouchDevice(){
    return true == ("ontouchstart" in window || window.DocumentTouch && document instanceof DocumentTouch);
}

$(document).ready(function() {
  $('#filter-sustainability').selectize({
      plugins: ['remove_button'],
      sortField: 'text'
  });

  $('[data-toggle=popover]').popover({
    html: true,
    trigger: 'hover'
  });

  $('[data-toggle=tooltip]').tooltip({
    html: true,
    trigger: 'hover'
  });

  if (isTouchDevice()) {
    $('.nav-tabs li').tooltip('disable');
  }
});
