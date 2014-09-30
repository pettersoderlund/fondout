Chart.defaults = {
  responsive: true
}

$(document).ready(function() {
  // Sustainability score dougnut chart
  $('.chart-container').each(function(index, container) {
    var canvas = container.querySelector('.sustainability-chart');
    var sustainability = canvas.dataset.percentage;
    var smallchart = canvas.dataset.smallchart;

    var options = {
      animateRotate : false,
      responsive: true,
      segmentShowStroke : true,
      segmentStrokeWidth : (smallchart ? 0 : 3),
      percentageInnerCutout : 0
    }

    var data = [
        {
            value: 20,
            color: ((sustainability > 0.1) ? "#A7D276" : "#e6e6e6")
        },
        {
            value: 20,
            color: ((sustainability > 0.3) ? "#A7D276" : "#e6e6e6")
        },
        {
            value: 20,
            color: ((sustainability > 0.5) ? "#A7D276" : "#e6e6e6")
        },
        {
            value: 20,
            color: ((sustainability > 0.7) ? "#A7D276" : "#e6e6e6")
        },
        {
            value: 20,
            color: ((sustainability > 0.9) ? "#A7D276" : "#e6e6e6")
        },
    ];

    var length = this.offsetWidth;
    $(canvas).attr("width",length).attr("height", length);

    new Chart(canvas.getContext("2d")).Doughnut(data, options);
  })
});
