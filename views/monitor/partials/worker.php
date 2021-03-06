<?php

use yii\web\View;
use spiritdead\resque\components\workers\base\ResqueWorkerBase;
use spiritdead\resque\plugins\schedule\ResqueScheduler;
use spiritdead\yii2resque\components\YiiResque;
use spiritdead\yii2resque\models\Job;
use spiritdead\yii2resque\models\mongo\Job as mongoJob;

/* @var $this yii\web\View */
/* @var $worker ResqueWorkerBase */
$processed = $worker->getStat('processed');
$failed = $worker->getStat('failed');
$success = $processed - $failed;
$queues = [];
if ($worker instanceof ResqueScheduler) {
    $queues = ['schedule'];
} else {
    $queues = $worker->queues(false);
}
$job = $worker->currentJob;
$jobID = '';
$timeStart = $worker->getStartTime();
if (isset($job->payload['args'][0][YiiResque::ACTION_META_KEY]['id'])) {
    $jobID = $job->payload['args'][0][YiiResque::ACTION_META_KEY]['id'];
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
        <div>
            <strong><?= (string)$worker ?></strong>
            <div class="pull-right">

            </div>
        </div>
        <div>
            <?= $processed . ' / ' . $success . ' / ' . $failed ?>
        </div>
        <div>
            <span>Total Processed / Total completed / Total Failed</span>
        </div>
        <div class="worker-currentjob">
            <?= Yii::t(
                'resque',
                'Current job [{id}]: {job}',
                ['id' => $jobID, 'job' => isset($job) ? $job : Yii::t('resque', 'Free')])
            ?>
        </div>
        <small class="text-muted">
            <?= date('d M y h:i:s a', $timeStart) ?>
            <span class="pull-right text-navy">
                <?= Yii::t('resque', 'Queue: {queue}', ['queue' => implode(',', $queues)]) ?>
            </span>
        </small>
    </div>
</div>