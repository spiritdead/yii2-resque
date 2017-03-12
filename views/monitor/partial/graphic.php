<?php

use backend\assets\InspiniaGraphChartJsAsset;
use yii\web\View;

/* @var $this yii\web\View */

InspiniaGraphChartJsAsset::register($this);

$this->registerJs('

');
?>
<div class="ibox float-e-margins">
    <div class="ibox-title">
        <h5>Jobs</h5>
        <div class="pull-right">
            <div class="btn-group">
                <button type="button" data-graph-btn-id="graph-live" class="btn btn-xs btn-white graph-btn">Realtime (every 30 secs)</button>
                <button type="button" data-graph-btn-id="graph-day" class="btn btn-xs graph-btn btn-white active">Last 24 hour</button>
                <button type="button" data-graph-btn-id="graph-week" class="btn btn-xs graph-btn btn-white">Weekly</button>
                <button type="button" data-graph-btn-id="graph-month" class="btn btn-xs graph-btn btn-white">Monthly</button>
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
