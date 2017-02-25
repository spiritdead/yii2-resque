<?php

namespace spiritdead\yii2resque\controllers;

use spiritdead\yii2resque\components\actions\DummyLongAction;
use spiritdead\yii2resque\models\Job;
use yii\web\Controller;
use yii;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;

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
        Yii::$app->yiiResque->createJob(DummyLongAction::class,['duration' => 15]);

        $currentWorkers = Yii::$app->yiiResque->getWorkers();
        $currentWorkerSchedulers = Yii::$app->yiiResque->getWorkerSchedulers();
        $currentWorkers = array_merge($currentWorkers, $currentWorkerSchedulers);
        $dataProvider = new ActiveDataProvider([
            'query' => Job::find()->orderBy(['id' => SORT_DESC])->limit(5),
            'pagination' => false
        ]);
        return $this->render('index', [
            'workers' => $currentWorkers,
            'dataProvider' => $dataProvider
        ]);
    }
}
