Chart.defaults = {
  responsive: true
}

$(document).ready(function() {
  var options = {
    animateRotate : false,
    responsive: true,
    percentageInnerCutout : 70
  }

  // Sustainability score dougnut chart
  $('.chart-container').each(function(index, container) {
    var canvas = container.querySelector('.sustainability-chart');
    var sustainability = canvas.dataset.percentage;

    var data = [
        {
            value: sustainability * 100,
            color: "#A7D276"
        },
        {
            value: (1 - sustainability) * 100,
            color:"#FF5252"
        }
    ];

    var length = this.offsetWidth;
    $(canvas).attr("width",length).attr("height", length);

    new Chart(canvas.getContext("2d")).Doughnut(data, options);
  })
});
