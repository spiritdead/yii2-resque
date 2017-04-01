$(document).ready(function () {

  var myChart;
  var barData;
  var xhrInt, xhrAjax;
  var graphMode;

  var initGraph = function () {
    barData = {
      datasets: [
        {
          label: 'Pending',
          //fill: false,
          //lineTension: 0,
          backgroundColor: 'rgba(54, 162, 235, 0.2)',
          borderColor: 'rgba(54, 162, 235, 1)',
          spanGaps: false
        },
        {
          label: 'Failed',
          //fill: false,
          //lineTension: 0.1,
          backgroundColor: 'rgba(255, 99, 132, 0.2)',
          borderColor: 'rgba(255,99,132,1)',
          spanGaps: false
        },
        {
          label: 'Success',
          //fill: false,
          //lineTension: 0.1,
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          borderColor: 'rgba(75, 192, 192, 1)',
          spanGaps: false
        },
        {
          label: 'Scheduled',
          //fill: false,
          //lineTension: 0.1,
          backgroundColor: 'rgba(255, 206, 86, 0.2)',
          borderColor: 'rgba(255, 206, 86, 1)',
          spanGaps: false
        }
      ]
    };
    var canv = $('#lineChart');
    canv.height = canv.parent('div')[0].offsetHeight;
    canv.width = canv.parent('div')[0].offsetWidth;
    var ctx = canv[0].getContext('2d');
    var barOptions = {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [{
          display: true,
          ticks: {
            suggestedMin: 0,
            beginAtZero: true
          }
        }]
      }
    };
    myChart = new Chart(ctx, {
      type: 'line',
      data: barData,
      options: barOptions,
      animation: {
        easing: 'linear'
      }
    });
  };
  var clearGraph = function (graph) {
    myChart.data.datasets.forEach(function (dataset, datasetIndex) {
      dataset.data.splice(0, dataset.data.length);
    });
  };
  var graphController = function graphic(mode) {
    graphMode = mode;
    if (typeof xhrInt !== 'undefined' && mode != 'graph-live') {
      clearInterval(xhrInt);
      xhrInt = undefined;
    }
    if (typeof xhrAjax !== 'undefined') {
      xhrAjax.abort();
      xhrAjax = undefined;
    }
    switch (mode) {
      case 'graph-live':
        if (typeof xhrInt === 'undefined') {
          clearGraph(myChart);
          myChart.data.labels.splice(0, myChart.data.labels.length);
          for (var index = 0; index <= 25; ++index) {
            myChart.data.labels.push(0);
            myChart.data.datasets.forEach(function (dataset, datasetIndex) {
              dataset.data.push(0);
            });
          }
          xhrInt = setInterval(function () {
            if (typeof xhrAjax !== 'undefined') {
              xhrAjax.abort();
              xhrAjax = undefined;
            }
            xhrAjax = $.ajax({
              url: '/resque/monitor/statistics?op=1',
              success: function (result) {
                if (myChart.data.labels.length > 10) {
                  myChart.data.labels.shift(); // remove the label first
                }
                myChart.data.datasets.forEach(function (dataset, datasetIndex) {
                  if (dataset.data.length > 10) {
                    dataset.data.shift();
                  }
                  dataset.data.push(result['response'][dataset.label][0]);
                });
                myChart.data.labels.push(myChart.data.labels[myChart.data.labels.length - 1] + 1);
                myChart.update();
                xhrAjax = undefined;
              },
              error: function (result) {
                xhrAjax = undefined;
              }
            });
          }, 30000);
        }
        break;
      case 'graph-day':
        clearGraph(myChart);
        myChart.data.labels = ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'];
        for (var index = 0; index <= 23; ++index) {
          myChart.data.datasets.forEach(function (dataset, datasetIndex) {
            dataset.data.push(0);
          });
        }
        xhrAjax = $.ajax({
          url: '/resque/monitor/statistics?op=2',
          success: function (result) {
            clearGraph(myChart);
            myChart.data.datasets.forEach(function (dataset, datasetIndex) {
              $.each(result['response'][dataset.label], function (key, value) {
                dataset.data.push(result['response'][dataset.label][key]);
              });
            });

            myChart.update();
            xhrAjax = undefined;
          },
          error: function (result) {
            xhrAjax = undefined;
          }
        });
        break;
      case 'graph-week':
        clearGraph(myChart);
        myChart.data.labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        for (var index = 0; index <= 6; ++index) {
          myChart.data.datasets.forEach(function (dataset, datasetIndex) {
            dataset.data.push(0);
          });
        }
        xhrAjax = $.ajax({
          url: '/resque/monitor/statistics?op=3',
          success: function (result) {
            clearGraph(myChart);
            myChart.data.datasets.forEach(function (dataset, datasetIndex) {
              $.each(result['response'][dataset.label], function (key, value) {
                dataset.data.push(result['response'][dataset.label][key]);
              });
            });

            myChart.update();
            xhrAjax = undefined;
          },
          error: function (result) {
            xhrAjax = undefined;
          }
        });
        break;
      case 'graph-month':
        clearGraph(myChart);
        myChart.data.labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        for (var index = 0; index <= 11; ++index) {
          myChart.data.datasets.forEach(function (dataset, datasetIndex) {
            dataset.data.push(0);
          });
        }
        xhrAjax = $.ajax({
          url: '/resque/monitor/statistics?op=4',
          success: function (result) {
            clearGraph(myChart);
            myChart.data.datasets.forEach(function (dataset, datasetIndex) {
              $.each(result['response'][dataset.label], function (key, value) {
                dataset.data.push(result['response'][dataset.label][key]);
              });
            });

            myChart.update();
            xhrAjax = undefined;
          },
          error: function (result) {
            xhrAjax = undefined;
          }
        });
        break;
    }
    myChart.update();
  };
  initGraph();
  graphController('graph-day');
  $('.graph-btn').on('click', function (e) {
    if (graphMode === this.dataset.graphBtnId) {
      return;
    }
    graphController(this.dataset.graphBtnId);
    $('.graph-btn').each(function (index) {
      $(this).removeClass('active');
    });
    $(this).addClass('active');
  });

  $(document).on('pjax:end', function (xhr, options) {
    switch (xhr.target.id) {
      case 'div-workers':
        $(xhr.target).find('.fa-refresh').removeClass('fa-spin');
        break;
    }
  });
  $(document).on('pjax:start', function (xhr, options) {
    switch (xhr.target.id) {
      case 'div-workers':
        $(xhr.target).find('.fa-refresh').addClass('fa-spin');
        break;
    }
  });
  $(document).on('pjax:success', function (xhr, data, status, options) {
    switch (xhr.target.id) {
      case 'form-wall':
        break;
    }
  });
  $(document).on('pjax:error', function (xhr, textStatus, error, options) {
    switch (xhr.target.id) {
      case 'div-worker':
        $(xhr.target).find('.fa-refresh').removeClass('fa-spin');
        break;
    }
  });
});