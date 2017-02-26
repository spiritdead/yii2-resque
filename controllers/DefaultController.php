<?php

namespace spiritdead\yii2resque\controllers;

use spiritdead\resque\components\workers\ResqueWorkerScheduler;
use spiritdead\resque\plugins\ResqueScheduler;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii;

/**
 * Default controller for the `resque` module
 */
class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => yii\filters\AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            $roleName = Yii::$app->user->identity->role->name;
                            return ($roleName == 'admin');
                        }
                    ],

                ],
            ]
        ];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        //Yii::$app->yiiResque->createJob(DummyAction::class, [], 'process');
        //Yii::$app->yiiResque->enqueueJobIn(5, DummyAction::class, []);
        $a = Yii::$app->yiiResque->getJobsCount();
        //$b = Yii::$app->yiiResque->resqueInstance->redis->zcard('queue:jobs');
        $c = Yii::$app->yiiResque->getDelayedJobsCount();
        $d = Yii::$app->yiiResque->resqueInstance->redis->llen('delayed_queue_schedule');
        $worker = new ResqueWorkerScheduler(new ResqueScheduler());
        $worker->work(2);
        return $this->render('index');
    }
}
