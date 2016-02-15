'use strict';
$(document).ready(function() {

  $('[data-toggle=popover]').popover({
    html: true,
    trigger: 'hover'
  });

  $('[data-toggle=tooltip]').tooltip({
    html: true,
    trigger: 'hover'
  });

});
