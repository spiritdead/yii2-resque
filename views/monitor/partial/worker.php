<?php

use yii\web\View;
use yii\helpers\Html;
use spiritdead\resque\components\YiiResque;
use spiritdead\resque\models\Job;
use spiritdead\resque\models\mongo\Job as mongoJob;

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
$job = $worker->job();
$jobID = '';
if(isset($job['payload']['args'][0][YiiResque::ACTION_META_KEY]['id'])) {
    $jobID = $job['payload']['args'][0][YiiResque::ACTION_META_KEY]['id'];
    $job = Job::findOne(['id' => $jobID]);
    $job = mongoJob::findOne(['_id' => $job->id_mongo]);
    $classShort = explode('\\', $job->class);
    if (count($classShort) > 0) {
        $classShort = $classShort[count($classShort) - 1];
    } else {
        $classShort = $job->class;
    }
    $job = ['class' => $classShort, 'action' => $job->action, 'data' => $job->data];
}
$job = json_encode($job);

?>
<div class="feed-element">
    <div>
        <small class="pull-right text-navy">
            <?= Yii::t('resque', 'Queue: {queue}', ['queue' => implode(',', $queues)]) ?>
        </small>
        <strong><?= (string)$worker ?></strong>
        <div>
            <?= $processed . ' / ' . $success . ' / ' . $failed ?>
        </div>
        <div>
            <span>Total Processed / Total completed / Total Failed</span>
        </div>
        <div class="worker-currentjob">
            <?= Yii::t('resque', 'Current job [{id}]: {job}',['id' => $jobID, 'job' => $job])?>
        </div>
        <small class="text-muted">Today 5:60 pm - 12.06.2014</small>
    </div>
</div>