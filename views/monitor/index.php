<?php

use yii\web\View;

/* @var $this yii\web\View */
/* @var $workers \Resque_Worker[] */

$this->title = Yii::t('backend', 'Job Monitor');
$this->params['description'][] = 'Panel de Control';

?>
<div class="row">
    <div class="col-md-12">
        <?= $this->render('partial/graphic', []) ?>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <?php if ($workers !== false): ?>
            <?php foreach ($workers as $worker): ?>
                <?= $this->render('partial/worker', ['worker' => $worker]) ?>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Not workers available</p>
        <?php endif; ?>
    </div>
    <div class="col-md-6">

    </div>
</div>
