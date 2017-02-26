<?php

namespace spiritdead\yii2resque\components;

use spiritdead\resque\components\workers\ResqueWorker;
use spiritdead\resque\components\workers\ResqueWorkerScheduler;
use spiritdead\resque\controllers\ResqueJobStatus;
use spiritdead\resque\models\ResqueBackend;
use spiritdead\resque\plugins\schedule\ResqueScheduler;
use spiritdead\resque\Resque;
use spiritdead\yii2resque\models\Job;
use spiritdead\yii2resque\models\mongo\Job as mongoJob;
use yii\base\Component;
use yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class YiiResque
 * @package spiritdead\yii2resque\components
 */
class YiiResque extends Component
{
    /**
     * @var null|Resque|ResqueScheduler
     */
    public $resqueInstance = null;

    /**
     * @var string Redis server address
     */
    public $server = 'localhost';

    /**
     * @var string Redis port number
     */
    public $port = '6379';

    /**
     * @var int Redis database index
     */
    public $database = 0;

    /**
     * @var string Redis password auth
     */
    public $password = '';

    /**
     * @const string The array key to use for the action meta data.
     */
    const ACTION_META_KEY = '_action';

    /**
     * @const string The async job class
     */
    const JOB_CLASS = AsyncActionJob::class;

    /**
     * Initializes the connection.
     */
    public function init()
    {
        parent::init();

        if ($this->server === null || $this->port === null) {
            throw new InvalidConfigException("Please define the server and the port in the config of the component");
        }
        $this->resqueInstance = new Resque(new ResqueBackend($this->server, $this->port, $this->database));
    }

    /**
     * Create a new job and save it to the specified queue.
     *
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function createJob(
        $class,
        $args = [],
        $jobAction = 'process',
        $queue = AsyncActionJob::QUEUE_NAME,
        $track_status = false
    ) {
        $params = $args;
        unset($args);
        $args['params'] = $params;
        unset($params);

        if (!isset($args[self::ACTION_META_KEY]) || !is_array($args[self::ACTION_META_KEY])) {
            $args[self::ACTION_META_KEY] = [];
        }

        $job = new Job();
        if (isset(Yii::$app->user) && !Yii::$app->user->isGuest) {
            $job->user_id = Yii::$app->user->id;
        }
        $mongoJob = new mongoJob();
        $mongoJob->class = $class;
        $mongoJob->action = $jobAction;
        $mongoJob->data = (array)$args;
        if ($mongoJob->save()) {
            $job->id_mongo = (string)$mongoJob->_id;
            $job->queue = $queue;
            if ($job->save()) {
                $args[self::ACTION_META_KEY] = ArrayHelper::merge($args[self::ACTION_META_KEY], [
                    'id' => $job->id,
                ]);
                $job->id_redis_job = $this->resqueInstance->enqueue($queue, self::JOB_CLASS, $args, $track_status);
                if ($job->update()) {
                    return $job->id_redis_job;
                }
            }
        }
        return false;
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param int $in Second count down to job.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     * @param string $jobAction action for do the job
     * @param string $queue The name of the queue to place the job in.
     *
     * @return string
     */
    public function enqueueJobIn(
        $in,
        $class,
        $args = [],
        $jobAction = 'process',
        $queue = AsyncActionJob::QUEUE_NAME
    ) {
        return $this->enqueueJobAt(time() + $in, $class, $args, $jobAction, $queue);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param \DateTime|int $at UNIX timestamp when job should be executed.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     * @param string $jobAction action for do the job
     * @param string $queue The name of the queue to place the job in.
     *
     * @return bool|void
     */
    public function enqueueJobAt(
        $at,
        $class,
        $args = [],
        $jobAction = 'process',
        $queue = AsyncActionJob::QUEUE_NAME
    ) {
        $params = $args;
        unset($args);
        $args['params'] = $params;
        unset($params);

        if (!isset($args[self::ACTION_META_KEY]) || !is_array($args[self::ACTION_META_KEY])) {
            $args[self::ACTION_META_KEY] = [];
        }

        $job = new Job();
        if (isset(Yii::$app->user) && !Yii::$app->user->isGuest) {
            $job->user_id = Yii::$app->user->id;
        }
        $job->scheduled = true;
        $job->scheduled_at = $at;
        $mongoJob = new mongoJob();
        $mongoJob->class = $class;
        $mongoJob->action = $jobAction;
        $mongoJob->data = (array)$args;
        if ($mongoJob->save()) {
            $job->id_mongo = (string)$mongoJob->_id;
            $job->queue = $queue;
            if ($job->save()) {
                $args[self::ACTION_META_KEY] = ArrayHelper::merge($args[self::ACTION_META_KEY], [
                    'id' => $job->id,
                ]);
                if ($this->resqueInstance instanceof ResqueScheduler) {
                    $this->resqueInstance->enqueueAt($at, $queue, self::JOB_CLASS, $args);
                } else {
                    $resque = new ResqueScheduler($this->resqueInstance->backend);
                    $resque->enqueueAt($at, $queue, self::JOB_CLASS, $args);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * Get delayed jobs count
     *
     * @return int
     */
    public function getDelayedJobsCount()
    {
        return (int)$this->resqueInstance->redis->zcard('delayed_queue_schedule');
    }

    /**
     * Get jobs count
     *
     * @return int
     */
    public function getJobsCount($queue = AsyncActionJob::QUEUE_NAME)
    {
        return (int)$this->resqueInstance->redis->llen('queue:' . $queue);
    }

    /**
     * Check job status
     *
     * @param string $token Job token ID
     *
     * @return string Job Status
     */
    public function status($token)
    {
        $status = new ResqueJobStatus($this->resqueInstance, $token);
        return $status->get();
    }

    /**
     * @param string $queueName
     * @return int
     */
    public function queueCount($queueName = AsyncActionJob::QUEUE_NAME)
    {
        return $this->resqueInstance->size($queueName);
    }

    /**
     * Get queues
     *
     * @return array
     */
    public function getQueues()
    {
        return $this->resqueInstance->queues();
    }

    /**
     * Delete a job based on job id or key, if worker_class is empty then it'll remove
     * all jobs within the queue, if job_key is empty then it'll remove all jobs within
     * provided queue and worker_class
     *
     * @param string $queue The name of the queue to place the job in.
     * @param string $worker_class The name of the class that contains the code to execute the job.
     * @param string $job_key Job key
     *
     * @return bool
     */
    public function deleteJob($queue, $worker_class = null, $job_key = null)
    {
        if (!empty($job_key) && !empty($worker_class)) {
            return $this->resqueInstance->dequeue($queue, [$worker_class => $job_key]);
        } // Remove job with specific job key
        else {
            if (!empty($worker_class) && empty($job_key)) {
                return $this->resqueInstance->dequeue($queue, [$worker_class]);
            } // Remove all jobs inside specified worker and queue
            else {
                return $this->resqueInstance->dequeue($queue);
            }
        } // Remove all jobs inside queue
    }

    /**
     * Delete the queue in redis
     *
     * @param string $queue
     * @return int
     */
    public function removeQueue($queue)
    {
        return $this->resqueInstance->removeQueue($queue);
    }

    /**
     * @param string $id
     * @return ResqueWorker|ResqueWorker[]|null
     */
    public function getWorkers($id = '')
    {
        if (empty($id)) {
            return ResqueWorker::all($this->resqueInstance);
        }
        $worker = ResqueWorker::find($this->resqueInstance, $id);
        if (!$worker) {
            return $worker;
        }
        return null;
    }

    /**
     * @param string $id
     * @return ResqueWorkerScheduler[]|ResqueWorkerScheduler|null
     */
    public function getWorkerSchedulers($id = '')
    {
        $instance = $this->resqueInstance;
        if($instance instanceof Resque){
            $instance = new ResqueScheduler($instance->backend);
        }
        if (empty($id)) {
            return ResqueWorkerScheduler::all($instance);
        }
        $worker = ResqueWorkerScheduler::find($instance, $id);
        if (!$worker) {
            return $worker;
        }
        return null;
    }
}