<?php

namespace spiritdead\yii2resque\commands;

use spiritdead\resque\components\jobs\base\ResqueJobBase;
use spiritdead\resque\components\workers\ResqueWorker;
use spiritdead\resque\plugins\schedule\workers\ResqueWorkerScheduler;
use spiritdead\resque\plugins\schedule\ResqueScheduler;
use spiritdead\yii2resque\components\actions\DummyAction;
use spiritdead\yii2resque\components\actions\DummyLongAction;
use spiritdead\yii2resque\components\actions\DummyErrorAction;
use spiritdead\yii2resque\components\AsyncActionJob;
use spiritdead\yii2resque\components\YiiResque;
use spiritdead\yii2resque\models\Job;
use spiritdead\yii2resque\models\mongo\Job as MongoJob;
use yii\console\Controller;
use yii;
use yii\base\Module;

/**
 * Controller for management of the jobs in queue.
 *
 * Class JobController
 * @package spiritdead\yii2resque\commands
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
        $this->stdout(Yii::t('resque', 'Starting job process on queue {queue}',
                ['queue' => $queues]) . PHP_EOL);

        // Instantiate the queues worker
        $queuesArray = explode(',', $queues);
        $worker = new ResqueWorker($this->_resque->resqueInstance, $queuesArray);
        $console = $this;
        $processed = 0;
        $this->_resque->resqueInstance->events->listen('beforePerform',
            function (ResqueJobBase $job) use ($console, &$processed) {

            }
        );
        $this->_resque->resqueInstance->events->listen('onFailure',
            function ($e, ResqueJobBase $job) use ($console, &$processed) {
                $processed++;
                /* @var AsyncActionJob $instance */
                $instance = $job->getInstance();
                /* @var $e \Exception */
                $classShort = explode('\\', $instance->_jobMongo['class']);
                if (count($classShort) > 0) {
                    $classShort = $classShort[count($classShort) - 1];
                } else {
                    $classShort = $instance->result['class'];
                }
                $logText = Yii::t('resque',
                        "Worker Job[{id}][{class}][{action}]: {success}\nQueue: {queue}\nMessage: {message}\nData: {data}",
                        [
                            'id' => $instance->_job->id,
                            'class' => $classShort,
                            'action' => $instance->_jobMongo['action'],
                            'success' => $instance->result['success'] ? 'Success' : 'Error',
                            'queue' => $instance->_job->queue,
                            'message' => $instance->result['message'],
                            'data' => json_encode($instance->args)
                        ]) . PHP_EOL;
                $errorText = Yii::t('resque', "Exception: {messageError} / line {lineError}", [
                        'messageError' => $e->getMessage(),
                        'lineError' => $e->getLine()
                    ]) . PHP_EOL;
                if ($processed == 1) {
                    $console->stdout("========================================================\n");
                }
                $console->stdout($logText);
                $console->stdout($errorText);
                $console->stdout("========================================================\n");

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
        $this->_resque->resqueInstance->events->listen('afterPerform',
            function (ResqueJobBase $job) use ($console, &$processed) {
                $processed++;
                /* @var AsyncActionJob $instance */
                $instance = $job->getInstance();
                $classShort = explode('\\', $instance->result['class']);
                if (count($classShort) > 0) {
                    $classShort = $classShort[count($classShort) - 1];
                } else {
                    $classShort = $instance->result['class'];
                }
                $logText = Yii::t('resque',
                        "Worker Job[{id}][{class}][{action}]: {success}\nQueue: {queue}\nMessage: {message}\nData: {data}",
                        [
                            'id' => $instance->_job->id,
                            'class' => $classShort,
                            'action' => $instance->result['action'],
                            'success' => $instance->result['success'] ? 'Success' : 'Error',
                            'queue' => $instance->_job->queue,
                            'message' => $instance->result['message'],
                            'data' => json_encode($instance->args)
                        ]) . PHP_EOL;
                $pendingText = Yii::t(
                        'resque',
                        "Worker: Processed {processed} / Pending jobs {pending}", [
                        'pending' => $console->_resque->getJobsCount(),
                        'processed' => $processed
                    ]) . PHP_EOL;
                $scheduledText = Yii::t('resque', "Job scheduled: {timeScheduled}", [
                        'timeScheduled' => date('d/m/Y h:i:s a', $instance->_job->scheduled_at)
                    ]) . PHP_EOL;
                $executedText = Yii::t('resque', "Job executed: {timeExecuted}", [
                        'timeExecuted' => date('d/m/Y h:i:s a', $instance->result['executed_at'])
                    ]) . PHP_EOL;
                $createdText = Yii::t('resque', "Job created: {timeCreated}", [
                        'timeCreated' => date('d/m/Y h:i:s a', $instance->_job->created_at)
                    ]) . PHP_EOL;
                $errorText = '';
                if (isset($instance->result['error'])) {
                    $errorText = Yii::t('resque', "Exception: {messageError} / line {lineError}", [
                            'messageError' => $instance->result['error']->getMessage(),
                            'lineError' => $instance->result['error']->getLine()
                        ]) . PHP_EOL;
                }

                if ($processed == 1) {
                    $console->stdout("========================================================\n");
                }
                $console->stdout($logText);
                $console->stdout($createdText);
                if ($instance->_job->scheduled) {
                    $console->stdout($scheduledText);
                }
                $console->stdout($executedText);
                if (isset($instance->result['error'])) {
                    $console->stdout($errorText);
                }
                $console->stdout($pendingText);
                $console->stdout("========================================================\n");
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
        $this->stdout("Starting scheduled job process" . PHP_EOL);

        // Instantiate the queues worker
        if ($this->_resque->resqueInstance instanceof ResqueScheduler) {
            $workerScheduler = new ResqueWorkerScheduler($this->_resque->resqueInstance);
        } else {
            $this->_resque->resqueInstance = new ResqueScheduler($this->_resque->resqueInstance->backend);
            $workerScheduler = new ResqueWorkerScheduler($this->_resque->resqueInstance);
        }

        $this->_resque->resqueInstance->events->listen('beforeDelayedEnqueue',
            function ($class, $args, $queue) use ($console) {
                $console->stdout(Yii::t('resque',
                        'WorkerScheduler: Job scheduled ID[{id}]: processed / Pending: {pending} / Queue: {queue}', [
                            'id' => $args[0][YiiResque::ACTION_META_KEY]['id'],
                            'queue' => $queue,
                            'pending' => $console->_resque->getDelayedJobsCount()
                        ]) . PHP_EOL);
            });
        $this->_resque->resqueInstance->events->listen('afterEnqueue',
            function ($class, $args, $queue, $id) use ($console) {
                $job = Job::findOne(['id' => $args[YiiResque::ACTION_META_KEY]['id']]);
                $job->id_redis_job = $id;
                $job->save();
                $console->stdout(Yii::t('resque', 'WorkerScheduler: Job ID[{id}]: was enqueued in the queue [{queue}]',
                        [
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
    public function actionClean($action = '')
    {
        switch ($action) {
            case 'purge':
                $this->stdout(Yii::t('resque', 'Cleaning Queues...') . PHP_EOL);
                foreach ($this->_resque->getQueues() as $queueName) {
                    if ($queueName != AsyncActionJob::QUEUE_NAME) {
                        $this->stdout(Yii::t('resque', 'Queue {queue} deleted', ['queue' => $queueName]) . PHP_EOL);
                        $this->_resque->removeQueue($queueName);
                    }
                }
                $this->stdout(Yii::t('resque', 'Cleaning Workers...') . PHP_EOL);
                $workers = array_merge($this->_resque->getWorkers(), $this->_resque->getWorkerSchedulers());
                /* @var $worker ResqueWorker | ResqueScheduler */
                foreach ($workers as $worker) {
                    if (is_object($worker)) {
                        list($host, $pid, $queues) = explode(':', (string)$worker, 3);
                        if (file_exists("/proc/$pid")) {
                            //process with a pid = $pid is running
                            shell_exec("kill -9 $pid");
                        }
                        $worker->unregisterWorker();
                        $this->stdout(Yii::t('resque', 'Worker {worker} stopped', ['worker' => $worker]) . PHP_EOL);
                    }
                }
                /* @var $worker ResqueWorker | ResqueScheduler */
                foreach ($workers as $worker) {
                    $this->stdout(Yii::t('resque', 'Worker {worker} deleted', ['worker' => $worker]) . PHP_EOL);
                    $worker->unregisterWorker();
                }
                break;
            case 'worker':
                $this->stdout(Yii::t('resque', 'Cleaning Workers...') . PHP_EOL);
                $workers = array_merge($this->_resque->getWorkers(), $this->_resque->getWorkerSchedulers());
                /* @var $worker ResqueWorker | ResqueScheduler */
                foreach ($workers as $worker) {
                    if (is_object($worker)) {
                        list($host, $pid, $queues) = explode(':', (string)$worker, 3);
                        if (file_exists("/proc/$pid")) {
                            //process with a pid = $pid is running
                            shell_exec("kill -9 $pid");
                        }
                        $worker->unregisterWorker();
                        $this->stdout(Yii::t('resque', 'Worker {worker} stopped', ['worker' => $worker]) . PHP_EOL);
                    }
                }
                /* @var $worker ResqueWorker | ResqueScheduler */
                foreach ($workers as $worker) {
                    $this->stdout(Yii::t('resque', 'Worker {worker} deleted in redis', ['worker' => $worker]) . PHP_EOL);
                    $worker->unregisterWorker();
                }
                break;
            case 'inactive':
                $workerPids = ResqueWorker::workerPids(); //are generics all of the PID of the computer
                $workers = array_merge($this->_resque->getWorkers(), $this->_resque->getWorkerSchedulers());
                /* @var $worker ResqueWorker | ResqueScheduler */
                foreach ($workers as $worker) {
                    if (is_object($worker)) {
                        list($host, $pid, $queues) = explode(':', (string)$worker, 3);
                        if (in_array($pid, $workerPids)) {
                            continue;
                        }
                        $worker->unregisterWorker();
                        $this->stdout(Yii::t('resque', 'Worker {worker} deleted', ['worker' => $worker]) . PHP_EOL);
                    }
                }
                break;
            default:
                $this->stdout(Yii::t('resque', 'Actions availables:') . PHP_EOL);
                $this->stdout(Yii::t('resque', '[purge] wipe all of the data in redis, destroy all of the workers') . PHP_EOL);
                $this->stdout(Yii::t('resque', '[inactive] clean the workers inactives in redis') . PHP_EOL);
                $this->stdout(Yii::t('resque', '[worker] kill and clean all of the workers') . PHP_EOL);
        }
    }

    /**
     * @param string $pid
     */
    public function actionStopWorker($pid = '*') {
        if ($pid == '*'){
            $this->stdout(Yii::t('resque', 'Worker stopping all of the workers') . PHP_EOL);
            $workers = array_merge($this->_resque->getWorkers(), $this->_resque->getWorkerSchedulers());
            /* @var $worker ResqueWorker | ResqueScheduler */
            foreach ($workers as $worker) {
                if (is_object($worker)) {
                    list($host, $pid, $queues) = explode(':', (string)$worker, 3);
                    if (file_exists("/proc/$pid")) {
                        //process with a pid = $pid is running
                        shell_exec("kill $pid");
                    }
                    $worker->unregisterWorker();
                    $this->stdout(Yii::t('resque', 'Worker {worker} stopped', ['worker' => $worker]) . PHP_EOL);
                }
            }
        } elseif (intval($pid)) {
            if (file_exists("/proc/$pid")) {
                //process with a pid = $pid is running
                shell_exec("kill $pid");
                $this->stdout(Yii::t('resque', 'Worker {worker} stopped', ['worker' => $pid]) . PHP_EOL);
            } else {
                $this->stdout(Yii::t('resque', 'Worker {worker} not found', ['worker' => $pid]) . PHP_EOL);
            }
        }
    }

    /**
     * Statistics in the Redis Server
     */
    public function actionStatistics()
    {
        $this->stdout(Yii::t('resque', 'Queues Pending: {pending}', [
                'pending' => $this->_resque->getJobsCount()
            ]) . PHP_EOL
        );
        $this->stdout(Yii::t('resque', 'Queues Scheduled: {scheduled}', [
                'scheduled' => $this->_resque->getDelayedJobsCount()
            ]) . PHP_EOL
        );
        $workers = array_merge($this->_resque->getWorkers(), $this->_resque->getWorkerSchedulers());
        foreach ($workers as $worker) {
            if (is_object($worker)) {
                $this->stdout(Yii::t('resque', 'Worker {worker} found', ['worker' => $worker]) . PHP_EOL);
            }
        }
    }

    /**
     * Re-enqueue jobs failed into his queue
     */
    public function actionRepeatJobs()
    {
        // Command example
        $console = $this;
        $this->stdout(Yii::t('resque', 'Repeating failed jobs') . PHP_EOL);
        $jobs = Job::find()->where(['result' => Job::RESULT_FAILED])->all();
        $this->_resque->resqueInstance->events->listen('beforeEnqueue',
            function ($class, $args, $queue, $id) use ($console) {
                if (!isset($args[YiiResque::ACTION_META_KEY]['id'])) {
                    $console->stdout(Yii::t('resque', 'Job not found: {params}', [
                            'params' => json_encode($args)
                        ]) . PHP_EOL
                    );
                    return;
                }
                $job = Job::findOne(['id' => $args[YiiResque::ACTION_META_KEY]['id']]);
                if ($job !== null) {
                    $job->result = Job::RESULT_NONE;
                    $job->result_message = null;
                    $job->executed_at = null;
                    $job->id_redis_job = $id;
                    $job->save();
                }
                $this->stdout(Yii::t('resque', 'Job id {id} restored and enqueued in {queue}', [
                        'id' => $args[YiiResque::ACTION_META_KEY]['id'],
                        'queue' => $queue
                    ]) . PHP_EOL
                );
            });
        /* @var $job Job */
        foreach ($jobs as $job) {
            $mongoJob = MongoJob::findOne(['_id' => $job->id_mongo]);
            if ($mongoJob !== null) {
                $this->_resque->resqueInstance->enqueue($job->queue, YiiResque::JOB_CLASS, $mongoJob->data, false);
            }
        }
    }

    /**
     * Tests for the server
     */
    public function actionTest()
    {
        // Command example
        $this->stdout(Yii::t('resque', 'Creating jobs dummy...') . PHP_EOL);
        $this->_resque->createJob(DummyErrorAction::class, []);
        $this->_resque->createJob(DummyErrorAction::class, []);
        $this->_resque->createJob(DummyLongAction::class, ['duration' => 15]);
        $this->_resque->createJob(DummyLongAction::class, []); //'duration' => 15
        $this->_resque->createJob(DummyAction::class, []);
        $this->_resque->enqueueJobIn(5, DummyAction::class, []);
        $this->_resque->enqueueJobIn(5, DummyErrorAction::class, []);
        $this->stdout(Yii::t('resque', "6 Dummy jobs created") . PHP_EOL);

        /*// For debug in mainThread
        $workerScheduler = new ResqueWorkerScheduler(new ResqueScheduler());
        $workerScheduler->handleDelayedItems();
        $worker = new ResqueWorker(Yii::$app->yiiResque->resqueInstance,['*']);
        // Start the worker
        $worker->work(2);*/
    }
}