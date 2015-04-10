'use strict';
$(document).ready(function() {

  $('#filter-company').selectize({
      plugins: ['remove_button'],
      sortField: 'text'
  });

  $('#filter-fund').selectize({
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
});

/* Denna funktion för sa sort by baren blir fixed nar man skrollar ner. */
$(document).ready(function () {
  var menu = $('.sort-menu');
  var origOffsetY = menu.offset().top;

  function scroll() {
      if ($(window).scrollTop() >= origOffsetY) {
          $('.sort-menu').addClass('navbar-fixed-top');
          $('.content').addClass('menu-padding');
      } else {
          $('.sort-menu').removeClass('navbar-fixed-top');
          $('.content').removeClass('menu-padding');
      }
  }
  document.onscroll = scroll;
});
