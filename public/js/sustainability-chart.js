'use strict';
$(document).ready(function() {
  // Sustainability score dougnut chart
  $('.chart-container').each(function(index, container) {
    var canvas = container.querySelector('.sustainability-chart');
    var sustainability = $(canvas).data("percentage");
    var smallchart = $(canvas).data("smallchart");
    var rotation = $(canvas).data("rotation");

    var options = {
      animateRotate : (rotation ? true : false),
      responsive: true,
      segmentShowStroke : true,
      segmentStrokeWidth : (smallchart ? 0 : 2),
      percentageInnerCutout : 0,
      animationSteps : 350,
      animationEasing : "easeOutQuart",
      //Boolean - Whether we animate scaling the Doughnut from the centre
      animateScale : false
    };

    var data = [
        {
            value: 10,
            color: ((sustainability > 0.0) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.1) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.2) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.3) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.4) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.5) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.6) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.7) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.8) ? '#A7D276' : '#e6e6e6')
        },
        {
            value: 10,
            color: ((sustainability > 0.9) ? '#A7D276' : '#e6e6e6')
        },

    ];

    var length = this.offsetWidth;
    $(canvas).attr('width',length).attr('height', length);

    new Chart(canvas.getContext('2d')).Doughnut(data, options);
  });
});
