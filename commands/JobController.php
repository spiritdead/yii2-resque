<?php

namespace spiritdead\resque\commands;

use spiritdead\resque\components\actions\DummyAction;
use spiritdead\resque\components\actions\DummyErrorAction;
use spiritdead\resque\components\base\AsyncActionJob;
use spiritdead\resque\components\YiiResque;
use Resque;
use Resque_Worker;
use yii\console\Controller;
use yii;
use yii\base\Module;

/**
 * Controller for management of the jobs in queue.
 *
 * Class JobController
 * @package spiritdead\resque\commands
 */
class JobController extends Controller
{
    /**
     * @var YiiResque
     */
    private $_resque;

    /**
     * JobController constructor.
     * @param string $id
     * @param Module $module
     * @param array $config
     */
    public function __construct($id, $module, array $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->_resque = Yii::$app->yiiResque;
    }

    /**
     * Action for process the jobs in queue.
     *
     * @param string $queues
     */
    public function actionProcess($queues = '*')
    {
        // Set various aliases
        printf("Starting job process on queue %s\n", $queues);

        // Instantiate the queues worker
        $queuesArray = explode(',', $queues);
        $worker = new Resque_Worker($queuesArray);
        $console = $this;
        $processed = 0;
        \Resque_Event::listen('afterPerform', function (\Resque_Job $job) use ($console, &$processed) {
            $processed++;
            /* @var $instance AsyncActionJob */
            $instance = $job->getInstance();
            $classShort = explode('\\', $instance->result['class']);
            if (count($classShort) > 0) {
                $classShort = $classShort[count($classShort) - 1];
            } else {
                $classShort = $instance->result['class'];
            }
            $logText = Yii::t('console',
                    "Worker Job[{id}][{class}][{action}]: {success}\nMessage: {message}\nData: {data}", [
                        'id' => $instance->_job->id,
                        'class' => $classShort,
                        'action' => $instance->result['action'],
                        'message' => $instance->result['message'],
                        'success' => $instance->result['success'] ? 'Success' : 'Error',
                        'data' => json_encode($instance->args)
                    ]) . PHP_EOL;
            $pendingText = Yii::t(
                    'console',
                    "Worker: Processed {processed} / Pending jobs {pending}", [
                    'pending' => $console->_resque->getJobsCount(),
                    'processed' => $processed
                ]) . PHP_EOL;
            $scheduledText = Yii::t('console', "Job scheduled: {timeScheduled}", [
                    'timeScheduled' => date('d/m/Y h:i:s a', $instance->_job->scheduled_at)
                ]) . PHP_EOL;
            $executedText = Yii::t('console', "Job executed: {timeExecuted}", [
                    'timeExecuted' => date('d/m/Y h:i:s a', $instance->result['executed_at'])
                ]) . PHP_EOL;
            $createdText = Yii::t('console', "Job created: {timeCreated}", [
                    'timeCreated' => date('d/m/Y h:i:s a', $instance->_job->created_at)
                ]) . PHP_EOL;
            $errorText = '';
            if (isset($instance->result['error'])) {
                $errorText = Yii::t('console', "Exception: {messageError} / line {lineError}", [
                        'messageError' => $instance->result['error']->getMessage(),
                        'lineError' => $instance->result['error']->getLine()
                    ]) . PHP_EOL;
            }

            if ($processed == 1) {
                $this->stdout("========================================================\n");
            }
            $this->stdout($logText);
            $this->stdout($createdText);
            if ($instance->_job->scheduled) {
                $this->stdout($scheduledText);
            }
            $this->stdout($executedText);
            if (isset($instance->result['error'])) {
                $this->stdout($errorText);
            }
            $this->stdout($pendingText);
            $this->stdout("========================================================\n");

            /*// Job arguments
            $emailParams = [
                'args' => $this->args,
                'exception' => $exception,
                'job' => $job,
            ];
            $debug = VarDumper::export($emailParams);

            // Send the email notification
            Yii::$app->mailer->compose('jobReport', ['debug' => $debug])
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::t('app', 'Alerts')])
                ->setTo(Yii::$app->params['notificationEmail'])
                ->setSubject(Yii::t('app', 'Bug report: Job has crashed ({0})', [strtoupper(YII_ENV)]))
                ->send();*/
        });
        // Start the worker
        $worker->work(2);
    }

    /**
     * Process scheduled jobs
     */
    public function actionProcessSchedule()
    {
        $console = $this;
        // Set various aliases
        $console->stdout("Starting scheduled job process" . PHP_EOL);

        // Instantiate the queues worker
        $workerScheduler = new \ResqueScheduler_Worker();
        \Resque_Event::listen('beforeDelayedEnqueue', function ($queue, $class, $args) use ($console) {
            $console->stdout(Yii::t('resque',
                    'WorkerScheduler: Job scheduled ID[{id}]: was processed / Pending: {pending}', [
                        'id' => $args[0][YiiResque::ACTION_META_KEY]['id'],
                        'queue' => $queue,
                        'pending' => $console->_resque->getDelayedJobsCount()
                    ]) . PHP_EOL);
        });
        \Resque_Event::listen('afterEnqueue', function ($class, $args, $queue) use ($console) {
            $console->stdout(Yii::t('resque', 'WorkerScheduler: Job ID[{id}]: was enqueued in the queue [{queue}]', [
                    'id' => $args[YiiResque::ACTION_META_KEY]['id'],
                    'queue' => $queue
                ]) . PHP_EOL);
        });
        // Start the worker
        $workerScheduler->work(2);
    }

    /**
     * Clean-up queues in the redis
     */
    public function actionClean()
    {
        $redisQueueInfo = $this->_resque->getQueues();
        var_dump($redisQueueInfo);
        foreach (Resque::queues() as $queueName) {
            if ($queueName != AsyncActionJob::QUEUE_NAME) {
                $this->_resque->removeQueue($queueName);
            }
        }
        var_dump(Resque::queues());
    }

    /**
     * Statistics in the Redis Server
     */
    public function actionStatistics()
    {
        $this->stdout(Yii::t('console', 'Queues Pending: {pending}', [
                'pending' => $this->_resque->getJobsCount()
            ]) . PHP_EOL
        );
        $this->stdout(Yii::t('console', 'Queues Scheduled: {scheduled}', [
                'scheduled' => $this->_resque->getDelayedJobsCount()
            ]) . PHP_EOL
        );
    }

    /**
     * Tests for the server
     */
    public function actionTest()
    {
        $this->_resque->createJob(DummyErrorAction::class, []);
        $this->_resque->createJob(DummyAction::class, []);
        $this->_resque->enqueueJobIn(5, DummyAction::class, []);
        $this->_resque->enqueueJobIn(5, DummyErrorAction::class, []);

        /*// For debug in mainThread
        $workerScheduler = new \ResqueScheduler_Worker();
        $workerScheduler->handleDelayedItems();
        $worker = new \Resque_Worker(['*']);
        // Start the worker
        $worker->work(2);*/
    }
}