<?php

use yii\web\View;
use yii\data\ActiveDataProvider;
use spiritdead\resque\components\workers\ResqueWorker;
use spiritdead\resque\plugins\schedule\ResqueScheduler;
use spiritdead\yii2resque\assets\MonitorAsset;
use yii\widgets\Pjax;
use yii\helpers\Html;

/* @var $this View */
/* @var $workers ResqueWorker[]|ResqueScheduler[] */
/* @var $dataProvider ActiveDataProvider */
MonitorAsset::register($this);

$this->title = Yii::t('backend', 'Job Monitor');
$this->params['description'][] = 'Panel de Control';

$workersWorking = 0;
$workersFree = 0;
foreach ($workers as $worker) {
    if ($worker->getWorking()) {
        $workersWorking++;
    } else {
        $workersFree++;
    }
}
?>
<div class="row">
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-info pull-right">Monthly</span>
                <h5><?= Yii::t('resque', 'Jobs') ?></h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">40.886,200</h1>
                <small><?= Yii::t('resque', 'Total jobs not processed') ?></small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-primary pull-right">Annual</span>
                <h5><?= Yii::t('resque', 'Jobs success') ?></h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">275,800</h1>
                <small><?= Yii::t('resque', 'Total jobs completed') ?></small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-danger pull-right">Today</span>
                <h5><?= Yii::t('resque', 'Jobs failed') ?></h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">106,120</h1>
                <small><?= Yii::t('resque', 'Total jobs failed') ?></small>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <span class="label label-success pull-right">Low value</span>
                <h5><?= Yii::t('resque', 'Jobs scheduled') ?></h5>
            </div>
            <div class="ibox-content">
                <h1 class="no-margins">80,600</h1>
                <small><?= Yii::t('resque', 'Total jobs scheduled') ?></small>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('partials/graphic', []) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-1"><i class="fa fa-users"></i></a></li>
                <li class=""><a data-toggle="tab" href="#tab-2"><i class="fa fa-check"></i></a></li>
                <li class=""><a data-toggle="tab" href="#tab-3"><i class="fa fa-times"></i></a></li>
                <li class=""><a data-toggle="tab" href="#tab-4"><i class="fa fa-clock-o"></i></a></li>
            </ul>
            <div class="tab-content">
                <div id="tab-1" class="tab-pane active">
                    <div class="panel-body">
                        <div class="text-center">
                            <strong><?= Yii::t('resque', 'Worker Manager') ?></strong>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?= Html::a(Yii::t('resque', 'Run worker'),
                                    ['run-worker', 'type' => 'normal'],
                                    [ 'class' => 'btn btn-success'])
                                ?>
                                <?= Html::a(Yii::t('resque', 'Run worker scheduler'),
                                    ['run-worker', 'type' => 'scheduler'],
                                    ['class' => 'btn btn-success'])
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="tab-2" class="tab-pane">
                    <div class="panel-body">
                        <div class="text-center">
                            <strong><?= Yii::t('resque', 'Job Manager') ?></strong>
                        </div>

                        <p>Thousand unknown plants are noticed by me: when I hear the buzz of the little
                            world among the stalks, and grow familiar with the countless indescribable forms
                            of the insects
                            and flies, then I feel the presence of the Almighty, who formed us in his own
                            image, and the breath </p>

                        <p>I am alone, and feel the charm of existence in this spot, which was created for
                            the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the
                            exquisite
                            sense of mere tranquil existence, that I neglect my talents. I should be
                            incapable of drawing a single stroke at the present moment; and yet.</p>
                    </div>
                </div>
                <div id="tab-3" class="tab-pane">
                    <div class="panel-body">
                        <strong>Donec quam felis</strong>

                        <p>Thousand unknown plants are noticed by me: when I hear the buzz of the little
                            world among the stalks, and grow familiar with the countless indescribable forms
                            of the insects
                            and flies, then I feel the presence of the Almighty, who formed us in his own
                            image, and the breath </p>

                        <p>I am alone, and feel the charm of existence in this spot, which was created for
                            the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the
                            exquisite
                            sense of mere tranquil existence, that I neglect my talents. I should be
                            incapable of drawing a single stroke at the present moment; and yet.</p>
                    </div>
                </div>
                <div id="tab-4" class="tab-pane">
                    <div class="panel-body">
                        <strong>Donec quam felis</strong>

                        <p>Thousand unknown plants are noticed by me: when I hear the buzz of the little
                            world among the stalks, and grow familiar with the countless indescribable forms
                            of the insects
                            and flies, then I feel the presence of the Almighty, who formed us in his own
                            image, and the breath </p>

                        <p>I am alone, and feel the charm of existence in this spot, which was created for
                            the bliss of souls like mine. I am so happy, my dear friend, so absorbed in the
                            exquisite
                            sense of mere tranquil existence, that I neglect my talents. I should be
                            incapable of drawing a single stroke at the present moment; and yet.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <?php Pjax::begin(['id' => 'div-workers', 'timeout' => 0]) ?>
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5><?= Yii::t('resque', 'Workers') ?></h5>
                <div class="ibox-tools">
                    <a href>
                        <i class="fa fa-refresh"></i>
                    </a>
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                    <a class="close-link">
                        <i class="fa fa-times"></i>
                    </a>
                </div>
            </div>
            <div class="ibox-content ibox-heading">
                <h3>
                    <i class="fa fa-briefcase"></i>
                    <?= Yii::t('resque',
                        '{n, plural, =0{# workers connected} =1{# worker connected} other{# workers connected}}',
                        ['n' => count($workers)]) ?>
                </h3>
                <small>
                    <i class="fa fa-tim"></i>
                    <?= Yii::t('resque',
                        'You have {n, plural, =0{# workers} =1{# worker} other{# workers}} working and {n1} free',
                        ['n' => $workersWorking, 'n1' => $workersFree]) ?>.
                </small>
            </div>
            <div class="ibox-content">
                <div class="feed-activity-list">
                    <?php if ($workers !== false): ?>
                        <?php foreach ($workers as $worker): ?>
                            <?= $this->render('partials/worker', ['worker' => $worker]) ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?= Yii::t('resque', 'Not workers available') ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php Pjax::end() ?>
    </div>
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>Job History</h5>

                        <div class="ibox-tools">
                            <a class="collapse-link">
                                <i class="fa fa-chevron-up"></i>
                            </a>
                            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-wrench"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-user">
                                <li>
                                    <a href="#">Config option 1</a>
                                </li>
                                <li>
                                    <a href="#">Config option 2</a>
                                </li>
                            </ul>
                            <a class="close-link">
                                <i class="fa fa-times"></i>
                            </a>
                        </div>
                    </div>
                    <div class="ibox-content ibox-heading">
                        <h3>You have Jobs to run!</h3>
                        <small><i class="fa fa-map-marker"></i> 0 incidents in the last hour
                        </small>
                    </div>
                    <div class="ibox-content inspinia-timeline job-history">
                        <?php foreach ($dataProvider->getModels() as $job): ?>
                            <?= $this->render('partials/history', ['job' => $job]) ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
