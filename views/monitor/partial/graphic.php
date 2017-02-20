<?php

use backend\assets\InspiniaGraphChartJsAsset;
use yii\web\View;

/* @var $this yii\web\View */

InspiniaGraphChartJsAsset::register($this);

$this->registerJs('
var barData = {
    labels: ["January", "February", "March", "April", "May", "June", "July"],
    datasets: [
        {
            label: "Failed",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(255, 99, 132, 0.2)",
            borderColor: "rgba(255,99,132,1)",
            data: [65, 59, 80, 81, 56, 55, 40],
            spanGaps: false,
        },
        {
            label: "Success",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(75, 192, 192, 0.2)",
            borderColor: "rgba(75, 192, 192, 1)",
            data: [50, 20, 50, 71, 90, 88, 10],
            spanGaps: false,
        },
        {
            label: "Scheduled",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(255, 206, 86, 0.2)",
            borderColor: "rgba(255, 206, 86, 1)",
            data: [15, 29, 40, 41, 96, 15, 30],
            spanGaps: false,
        }
    ]
    };

    var barOptions = {
        responsive: true,
        maintainAspectRatio: false
    }


    var canv = $("#lineChart");
    canv.height = canv.parent("div")[0].offsetHeight;
    canv.width = canv.parent("div")[0].offsetWidth;
    var ctx = canv[0].getContext("2d");
    var myNewChart = new Chart(ctx, {
        type: "line",
        data: barData,
        options: barOptions
    });
');
?>
<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5>Jobs</h5>
        <div class="pull-right">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-white active">Last 24 hour</button>
                <button type="button" class="btn btn-xs btn-white">Weekly</button>
                <button type="button" class="btn btn-xs btn-white">Monthly</button>
                <button type="button" class="btn btn-xs btn-white">Annual</button>
            </div>
        </div>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-9">
                <div class="flot-chart">
                    <div>
                        <canvas id="lineChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <ul class="stat-list">
                    <li>
                        <h2 class="no-margins">2,346</h2>
                        <small>Total jobs in period</small>
                        <div class="stat-percent">48% <i class="fa fa-level-up text-navy"></i></div>
                        <div class="progress progress-mini">
                            <div style="width: 48%;" class="progress-bar"></div>
                        </div>
                    </li>
                    <li>
                        <h2 class="no-margins ">4,422</h2>
                        <small>Jobs in last month</small>
                        <div class="stat-percent">60% <i class="fa fa-level-down text-navy"></i>
                        </div>
                        <div class="progress progress-mini">
                            <div style="width: 60%;" class="progress-bar"></div>
                        </div>
                    </li>
                    <li>
                        <h2 class="no-margins ">9,180</h2>
                        <small>Scheduled jobs in last month</small>
                        <div class="stat-percent">22% <i class="fa fa-bolt text-navy"></i></div>
                        <div class="progress progress-mini">
                            <div style="width: 22%;" class="progress-bar"></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
