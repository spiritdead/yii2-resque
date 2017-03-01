<?php

namespace spiritdead\yii2resque\controllers;

use spiritdead\resque\components\workers\ResqueWorker;
use spiritdead\resque\plugins\schedule\ResqueScheduler;
use spiritdead\resque\plugins\schedule\workers\ResqueWorkerScheduler;
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
        $a = Yii::$app->yiiResque->getWorkers();
        return $this->render('index');
    }
}
