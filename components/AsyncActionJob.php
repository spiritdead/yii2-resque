<?php

namespace spiritdead\yii2resque\components;

use spiritdead\resque\components\jobs\base\ResqueJobBase;
use spiritdead\resque\components\jobs\ResqueJob;
use spiritdead\resque\components\jobs\ResqueJobInterface;
use spiritdead\resque\exceptions\ResqueJobPerformException;
use spiritdead\yii2resque\models\Job;
use spiritdead\yii2resque\models\LogJob;
use spiritdead\yii2resque\models\mongo\Job as mongoJob;
use yii;
use yii\helpers\ArrayHelper;

/**
 * Class AsyncActionJob
 * @package spiritdead\yii2resque\components\base
 */
class AsyncActionJob extends ResqueJob implements ResqueJobInterface
{
    /**
     * @var null|Job
     */
    public $_job = null;

    /**
     * @var null | mongoJob
     */
    public $_jobMongo = null;

    /**
     * @const string name of the queue to process jobs
     */
    const QUEUE_NAME = 'jobs';

    /**
     * @throws ResqueJobPerformException
     */
    public function setUp()
    {
        // Retrieve the job from the database mysql
        $this->_job = Job::findOne(['id' => $this->args[YiiResque::ACTION_META_KEY]['id']]);
        // Retrieve the job from the database mongoDB
        $this->_jobMongo = mongoJob::findOne(['_id' => $this->_job->id_mongo]);

        if ($this->_job === null) {
            throw new ResqueJobPerformException(Yii::t('resque', 'Job not found in database mysql.'));
        } elseif($this->_jobMongo === null) {
            throw new ResqueJobPerformException(Yii::t('resque', 'Job not found in database mongoDB.'));
        }

        $this->job->resqueInstance->events->listen('onFailure', function ($e, ResqueJobBase $job) {
            /* @var $instance AsyncActionJob */
            /* @var $e \Exception */
            $instance = $job->getInstance();
            $this->result = [
                'action' => $this->_jobMongo['action'],
                'class' => $this->_jobMongo['class'],
                'success' => false,
                'message' => $e->getMessage(),
                'executed_at' => time(),
                'error' => $e
            ];
            $instance->_job->result = $instance->result['success'] ? Job::RESULT_SUCCESS : Job::RESULT_FAILED;
            $instance->_job->result_message = $instance->result['message'];
            $instance->_job->executed_at = $instance->result['executed_at'];
            $instance->_job->save();
            // Log the job error
            if (isset($instance->result['error'])) {
                $contentError = [
                    'haveException' => true,
                    'exception' => [
                        'message' => $instance->result['error']->getMessage(),
                        'code' => $instance->result['error']->getCode(),
                        'file' => $instance->result['error']->getFile(),
                        'line' => $instance->result['error']->getLine(),
                        'trace' => $instance->result['error']->getTraceAsString(),
                    ],
                    'job' => [
                        'status' => $job->status->get(),
                        'args' => $job->getArguments(),
                        'instance' => $instance,
                    ],
                ];
            } else {
                $contentError = [
                    'haveException' => false,
                    'exception' => [
                        'message' => $instance->result['message'],
                    ],
                    'job' => [
                        'status' => $job->status->get(),
                        'args' => $job->getArguments(),
                        'instance' => $instance,
                    ],
                ];
            }
            // Log the error
            LogJob::log(
                LogJob::CATEGORY_RUNTIME_EXCEPTION,
                $instance->args[YiiResque::ACTION_META_KEY]['id'],
                false,
                $contentError
            );
        });
        $this->job->resqueInstance->events->listen('afterPerform', function (ResqueJobBase $job) {
            /* @var $instance AsyncActionJob */
            $instance = $job->getInstance();
            $instance->_job->result = $instance->result['success'] ? Job::RESULT_SUCCESS : Job::RESULT_FAILED;
            $instance->_job->result_message = $instance->result['message'];
            $instance->_job->executed_at = $instance->result['executed_at'];
            $instance->_job->save();
            // Log the job result
            LogJob::log(
                LogJob::CATEGORY_SUCCESS,
                $instance->args[YiiResque::ACTION_META_KEY]['id'],
                $instance->result['success'],
                ArrayHelper::merge([
                    'result' => $instance->result,
                ], $instance->args)
            );
            //$instance->_job->delete(); for the moment dont delete the completed jobs
        });
    }

    /**
     * Execute the job/action.
     */
    public function perform()
    {
        // Call the job
        if (isset($this->_jobMongo['class']) && strlen($this->_jobMongo['class']) > 0) {
            $func = '\\' . $this->_jobMongo['class'] . '::' . $this->_jobMongo['action'];
            $this->result = call_user_func($func, $this->args['params']);
            $this->result['class'] = $this->_jobMongo['class'];
            $this->result['action'] = $this->_jobMongo['action'];
            if(!$this->result['success']) {
                throw new ResqueJobPerformException($this->result['message']);
            }
        }
    }

    /**
     * After Perform
     */
    public function tearDown()
    {

    }
}