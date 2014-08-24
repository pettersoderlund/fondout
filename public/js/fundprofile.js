Chart.defaults = {
  responsive: true
}

$(document).ready(function() {
  $('#filter-sustainability').selectize({
      plugins: ['remove_button'],
      sortField: 'text'
  });


  // Sustainability score dougnut chart
  var canvas = document.querySelector('#sustainability-chart');
  var sustainability = canvas.dataset.percentage;
  var ctx = canvas.getContext("2d");

  console.log(sustainability);

  var data = [
      {
          value: sustainability * 100,
          color: "#5cb85c"
      },
      {
          value: (1 - sustainability) * 100,
          color:"#d9534f"
      }
  ]

  var options = {
    animateRotate : false,
    responsive: true
  }


  var length = $('#sustainability-chart').parent().width();
  console.log(length);
  $('#sustainability-chart').attr("width",length).attr("height", length);

  new Chart(ctx).Doughnut(data, options);

  // window.onresize = function(event){
  //     var width = $('#sustainability-chart').parent().width();
  //     $('#sustainability-chart').attr("width",width);
  //     new Chart(ctx).Line(data,options);
  // };
})
