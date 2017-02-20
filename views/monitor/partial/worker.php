<?php

use yii\web\View;

/* @var $this yii\web\View */
/* @var $worker \Resque_Worker */
$processed = $worker->getStat('processed');
$failed = $worker->getStat('failed');
$success = $processed - $failed;
$queues = [];
if ($worker instanceof ResqueScheduler_Worker) {
    $queues = ['schedule'];
} else {
    $queues = $worker->queues(false);
}
?>
<div class="ibox float-e-margins">
    <div class="ibox-title">
        <span class="label label-success pull-right"><?= Yii::t('resque', 'Queue: {queue}',
                ['queue' => implode(',', $queues)]) ?></span>
        <h5><?= (string)$worker ?></h5>
    </div>
    <div class="ibox-content">
        <h1 class="no-margins"><?= $processed . ' / ' . $success . ' / ' . $failed ?></h1>
        <div class="stat-percent font-bold text-success">98% <i class="fa fa-bolt"></i></div>
        <small>Total Processed / Total completed / Total Failed</small>
    </div>
</div>
