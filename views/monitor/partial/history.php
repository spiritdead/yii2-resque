<?php

use yii\web\View;
use yii\helpers\Html;
use spiritdead\resque\models\Job;
use spiritdead\resque\models\mongo\Job as mongoJob;

/* @var $this View */
/* @var $job Job */
/* @var $mongoJob mongoJob */

$mongoJob = mongoJob::findOne(['_id' => $job->id_mongo]);
$icon = '';
$color = '';
$timeText = '';
$class = explode('\\', $mongoJob->class);
if (is_array($class) && count($class) > 0) {
    $class = $class[count($class) - 1];
} else {
    $class = '';
}
switch ($job->result) {
    case Job::RESULT_SUCCESS:
        $icon = '<i class="fa fa-check" aria-hidden="true"></i>';
        $color = ' job-success';
        $timeText = Yii::t('resque', 'Created At {date}', ['date' => date('d/m/Y h:i:s', $job->created_at)]);
        break;
    case Job::RESULT_FAILED:
        $icon = '<i class="fa fa-times" aria-hidden="true"></i>';
        $color = ' job-failed';
        $timeText = Yii::t('resque', 'Executed At {date}', ['date' => date('d/m/Y h:i:s', $job->executed_at)]);
        break;
    case Job::RESULT_NONE:
        if ($job->scheduled) {
            $icon = '<i class="fa fa-clock-o" aria-hidden="true"></i>';
            $color = ' job-scheduled';
            $timeText = Yii::t('resque', 'Scheduled At {date}', ['date' => date('d/m/Y h:i:s', $job->scheduled_at)]);
        } else {
            $icon = '<i class="fa fa-gear" aria-hidden="true"></i>';
            $color = ' job-primary';
        }
        break;
}
?>

<div class="timeline-item<?= $color ?>">
    <div class="clearfix">
        <div class="col-md-3 date">
            <?= $icon ?>
            6:00 am
            <br/>
            <small class="text-navy">
                <?= '' ?>
            </small>
        </div>
        <div class="col-md-9 content">
            <p class="m-b-xs">
                <strong>
                    Job N <?= Html::encode($job->id . ' ' . $class . ':' . ucfirst($mongoJob->action)) ?>
                </strong>
            </p>
            <p>
                <?= Html::encode(
                    !empty($job->result_message) ? $job->result_message : ($job->result ? Yii::t('resque',
                        'Result not obtained') : Yii::t('resque', 'Pending for process'))
                ) ?>
            </p>
            <p>
                <?= $timeText ?>
            </p>
        </div>
    </div>
</div>
