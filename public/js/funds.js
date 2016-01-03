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

  /*
  var visited = $.cookie('visited'); // create the cookie
  if (visited == 'yes') {
      return false; // second page load, cookie is active so do nothing
  } else {
      $('#howToSearchModal').modal();; // first page load, launch modal
  };
  // assign cookie's value and expiration time
  $.cookie('visited', 'yes', {
      expires: 1 // the number of days the cookie will be effective
  });
  */


});

/* This functions makes the scrollbar fixed to the top as scrolling down */
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
