<?php

namespace spiritdead\resque\components;

use spiritdead\resque\components\base\AsyncActionJob;
use common\models\Job;
use common\models\mongo\Job as mongoJob;
use yii\base\Component;
use Yii;

//use resque\lib\ResqueAutoloader;
//use resque\lib\Resque;
//use resque\lib\ResqueScheduler;

use Resque;
use Resque_Job_Status;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class YiiResque
 * @package common\components\job
 */
class YiiResque extends Component
{
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


    public $prefix = '';

    /**
     * @const string The array key to use for the action meta data.
     */
    const ACTION_META_KEY = '_action';
    /**
     * @const string The async job class
     */
    const JOB_CLASS = '\\common\\components\\job\\base\\AsyncActionJob';

    /**
     * Initializes the connection.
     */
    public function init()
    {
        parent::init();

        if ($this->server === null || $this->port === null) {
            throw new InvalidConfigException("Please define the server and the port in the config of the component");
        }

        Resque::setBackend($this->server . ':' . $this->port);
        if ($this->prefix) {
            Resque::redis()->prefix($this->prefix);
        }

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
        $mongoJob->save();
        $job->id_mongo = (string)$mongoJob->_id;
        if ($job->save()) {
            $args[self::ACTION_META_KEY] = ArrayHelper::merge($args[self::ACTION_META_KEY], [
                'id' => $job->id,
            ]);
            return Resque::enqueue($queue, self::JOB_CLASS, $args, $track_status);
        } else {
            return false;
        }
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
        return self::enqueueJobAt(time() + $in, $class, $args, $jobAction, $queue);
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
        $mongoJob->save();
        $job->id_mongo = (string)$mongoJob->_id;
        if ($job->save()) {
            $args[self::ACTION_META_KEY] = ArrayHelper::merge($args[self::ACTION_META_KEY], [
                'id' => $job->id,
            ]);
            return \ResqueScheduler::enqueueAt($at, $queue, self::JOB_CLASS, $args);
        } else {
            return false;
        }
    }

    /**
     * Get delayed jobs count
     *
     * @return int
     */
    public function getDelayedJobsCount()
    {
        return (int)Resque::redis()->zcard('delayed_queue_schedule');
    }

    /**
     * Get jobs count
     *
     * @return int
     */
    public function getJobsCount($queue = AsyncActionJob::QUEUE_NAME)
    {
        //return (int)Resque::redis()->zcard(AsyncActionJob::QUEUE_NAME);
        return (int)Resque::redis()->llen('queue:' . $queue);
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
        $status = new Resque_Job_Status($token);
        return $status->get();
    }

    /**
     * Return Redis
     *
     * @return object Redis instance
     */
    public function redis()
    {
        return Resque::redis();
    }

    /**
     * @param string $queueName
     * @return int
     */
    public function queueCount($queueName = AsyncActionJob::QUEUE_NAME)
    {
        return Resque::size($queueName);
    }

    /**
     * Get queues
     *
     * @return array
     */
    public function getQueues()
    {
        return Resque::queues();
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
            return Resque::dequeue($queue, array($worker_class => $job_key));
        } // Remove job with specific job key
        else {
            if (!empty($worker_class) && empty($job_key)) {
                return Resque::dequeue($queue, array($worker_class));
            } // Remove all jobs inside specified worker and queue
            else {
                return Resque::dequeue($queue);
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
        return Resque::removeQueue($queue);
    }
}