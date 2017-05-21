<?php

namespace spiritdead\yii2resque\controllers;

use spiritdead\yii2resque\components\filters\AjaxControl;
use spiritdead\yii2resque\helpers\TimeHelper;
use spiritdead\yii2resque\models\Job;
use yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Class MonitorController
 * @package spiritdead\yii2resque\controllers
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
                        'actions' => ['index', 'statistics', 'run-worker', 'stop-worker'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            $roleName = Yii::$app->user->identity->role->name;
                            return ($roleName == 'admin');
                        }
                    ],
                ],
            ],
            'ajax' => [
                'class' => AjaxControl::className(),
                'only' => ['statistics']
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
        $dataProvider = new ActiveDataProvider([
            'query' => Job::find()->orderBy(['id' => SORT_DESC])->limit(5),
            'pagination' => false
        ]);
        return $this->render('index', [
            'workers' => $currentWorkers,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     *
     */
    public function actionStopWorker($worker)
    {
        //return Yii::$app->yiiResque->runner->runAsync('resque/job/stop-worker');
    }

    /**
     * @param string $type
     * @return Response
     */
    public function actionRunWorker($type = 'normal')
    {
        switch ($type) {
            case 'normal':
                Yii::$app->yiiResque->runner->runAsync('resque/job/process');
                break;
            case 'scheduler':
                Yii::$app->yiiResque->runner->runAsync('resque/job/process-schedule');
                break;
        }
        return $this->redirect(['monitor/index']);
    }

    /**
     * Ajax for refresh the statistics in the index
     * @param $op
     * @return array
     */
    public function actionStatistics($op)
    {
        $jobs = [
            'Pending' => [],
            'Failed' => [],
            'Success' => [],
            'Scheduled' => []
        ];
        switch ($op) {
            case 1: // Realtime
                $time = strtotime('now');
                // TimeInterval
                $tI = (30); //25 * 30
                $data = (new yii\db\Query())
                    ->select([
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' ,1,0)),0) AS Pending',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_SUCCESS . ' ,1,0)),0) AS Success',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_FAILED . ' ,1,0)),0) AS Failed',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' and scheduled = true ,1,0)),0) AS Scheduled'
                    ])
                    ->from(Job::tableName())
                    ->where(['between', 'created_at', $time - $tI, $time])
                    ->all();
                foreach ($data as $key => $row) {
                    $jobs['Pending'][$key] = $row['Pending'];
                    $jobs['Failed'][$key] = $row['Failed'];
                    $jobs['Success'][$key] = $row['Success'];
                    $jobs['Scheduled'][$key] = $row['Scheduled'];
                }
                break;
            case 2: // Today
                $GMT = TimeHelper::getStandardOffsetUTC(Yii::$app->timeZone);
                $data = (new yii\db\Query())
                    ->select([
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' ,1,0)),0) AS Pending',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_SUCCESS . ' ,1,0)),0) AS Success',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_FAILED . ' ,1,0)),0) AS Failed',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' and scheduled = true ,1,0)),0) AS Scheduled',
                        'HOUR(CONVERT_TZ(FROM_UNIXTIME(created_at), "+00:00", "' . $GMT . '")) as hr'
                    ])
                    ->from(Job::tableName())
                    ->where(['>', 'created_at', strtotime(date('00:00') . ' UTC')])
                    ->groupBy('hr')
                    ->orderBy(['hr' => SORT_ASC])
                    ->all();
                for ($i = 0; $i <= 23; $i++) {
                    $jobs['Pending'][$i] = 0;
                    $jobs['Failed'][$i] = 0;
                    $jobs['Success'][$i] = 0;
                    $jobs['Scheduled'][$i] = 0;
                }
                foreach ($data as $row) {
                    $jobs['Pending'][$row['hr']] = (int)$row['Pending'];
                    $jobs['Failed'][$row['hr']] = (int)$row['Failed'];
                    $jobs['Success'][$row['hr']] = (int)$row['Success'];
                    $jobs['Scheduled'][$row['hr']] = (int)$row['Scheduled'];
                }
                break;
            case 3: // Week
                $GMT = TimeHelper::getStandardOffsetUTC(Yii::$app->timeZone);
                $data = (new yii\db\Query())
                    ->select([
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' ,1,0)),0) AS Pending',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_SUCCESS . ' ,1,0)),0) AS Success',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_FAILED . ' ,1,0)),0) AS Failed',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' and scheduled = true ,1,0)),0) AS Scheduled',
                        'WEEKDAY(CONVERT_TZ(FROM_UNIXTIME(created_at), "+00:00", "' . $GMT . '")) as wd'
                    ])
                    ->from(Job::tableName())
                    ->where(['>', 'created_at', strtotime(date('w'))])
                    ->groupBy('wd')
                    ->orderBy(['wd' => SORT_ASC])
                    ->all();
                for ($i = 1; $i <= 7; $i++) {
                    $jobs['Pending'][$i] = 0;
                    $jobs['Failed'][$i] = 0;
                    $jobs['Success'][$i] = 0;
                    $jobs['Scheduled'][$i] = 0;
                }
                foreach ($data as $row) {
                    $jobs['Pending'][$row['wd']] = (int)$row['Pending'];
                    $jobs['Failed'][$row['wd']] = (int)$row['Failed'];
                    $jobs['Success'][$row['wd']] = (int)$row['Success'];
                    $jobs['Scheduled'][$row['wd']] = (int)$row['Scheduled'];
                }
                break;
            case 4: // Month
                $GMT = TimeHelper::getStandardOffsetUTC(Yii::$app->timeZone);
                $data = (new yii\db\Query())
                    ->select([
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' ,1,0)),0) AS Pending',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_SUCCESS . ' ,1,0)),0) AS Success',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_FAILED . ' ,1,0)),0) AS Failed',
                        'COALESCE(SUM(IF(result = ' . Job::RESULT_NONE . ' and scheduled = true ,1,0)),0) AS Scheduled',
                        'MONTH(CONVERT_TZ(FROM_UNIXTIME(created_at), "+00:00", "' . $GMT . '")) as mt'
                    ])
                    ->from(Job::tableName())
                    ->where(['>', 'created_at', strtotime(date('y'))])
                    ->groupBy('mt')
                    ->orderBy(['mt' => SORT_ASC])
                    ->all();
                for ($i = 1; $i <= 12; $i++) {
                    $jobs['Pending'][$i] = 0;
                    $jobs['Failed'][$i] = 0;
                    $jobs['Success'][$i] = 0;
                    $jobs['Scheduled'][$i] = 0;
                }
                foreach ($data as $row) {
                    $jobs['Pending'][$row['mt']] = (int)$row['Pending'];
                    $jobs['Failed'][$row['mt']] = (int)$row['Failed'];
                    $jobs['Success'][$row['mt']] = (int)$row['Success'];
                    $jobs['Scheduled'][$row['mt']] = (int)$row['Scheduled'];
                }
                break;
        }
        return [
            'success' => true,
            'response' => $jobs
        ];
    }
}
