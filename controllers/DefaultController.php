<?php

namespace spiritdead\yii2resque\controllers;

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
        return $this->render('index');
    }
}
