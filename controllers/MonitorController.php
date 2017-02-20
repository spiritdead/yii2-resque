<?php

namespace spiritdead\resque\controllers;

use spiritdead\resque\models\Job;
use yii\web\Controller;
use yii;
use yii\filters\AccessControl;

/**
 * Job controller for the `resque` module
 */
class MonitorController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
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
        $currentWorkers = Yii::$app->yiiResque->getWorkers();
        $currentWorkerSchedulers = Yii::$app->yiiResque->getWorkerSchedulers();
        $currentWorkers = array_merge($currentWorkers, $currentWorkerSchedulers);
        $jobs = Job::find()->all();
        return $this->render('index', [
            'workers' => $currentWorkers
        ]);
    }
}
