<?php

namespace spiritdead\yii2resque\components\base;

use spiritdead\yii2resque\components\YiiResque;
use spiritdead\yii2resque\components\base\Job as baseJob;
use spiritdead\yii2resque\models\Job;
use spiritdead\yii2resque\models\LogJob;
use spiritdead\yii2resque\models\mongo\Job as mongoJob;
use yii;
use Resque_Event;
use Resque_Job_DontPerform;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

/**
 * Class AsyncActionJob
 * @package spiritdead\yii2resque\components\base
 */
class AsyncActionJob_old extends baseJob implements JobInterface
{
    /**
     * @const string name of the queue to process jobs
     */
    const QUEUE_NAME = 'jobs';

    /**
     * @throws Resque_Job_DontPerform
     */
    public function setUp()
    {
        try {
            $this->_job = Job::findOne(['id' => $this->args[YiiResque::ACTION_META_KEY]['id']]);
        } catch (\Exception $exception) {
            $this->logError(
                LogJob::CATEGORY_RUNTIME_EXCEPTION,
                $this->args[YiiResque::ACTION_META_KEY]['id'],
                false,
                [
                    'exception' => [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => $exception->getTraceAsString(),
                    ]
                ],
                false
            );
        }
        Resque_Event::listen('afterPerform', function (\Resque_Job $job) {
            /* @var $instance AsyncActionJob */
            $instance = $job->getInstance();
            $instance->_job->result = $instance->result['success'] ? Job::RESULT_SUCCESS : Job::RESULT_FAILED;
            $instance->_job->result_message = $instance->result['message'];
            $instance->_job->executed_at = $instance->result['executed_at'];
            $instance->_job->save();
            if ($instance->result['success']) {
                // Log the job result
                $instance->logResult(
                    LogJob::CATEGORY_SUCCESS,
                    $instance->args[YiiResque::ACTION_META_KEY]['id'],
                    $instance->result['success'],
                    ArrayHelper::merge([
                        'result' => $instance->result,
                    ], $instance->args)
                );
                //$instance->_job->delete(); for the moment dont delete the completed jobs
            } else {
                \Resque_Stat::incr('failed');
                \Resque_Stat::incr('failed:' . $job->worker);
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
                            'status' => $job->getStatus(),
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
                            'status' => $job->getStatus(),
                            'args' => $job->getArguments(),
                            'instance' => $instance,
                        ],
                    ];
                }
                $instance->logError(
                    LogJob::CATEGORY_RUNTIME_EXCEPTION,
                    $instance->args[YiiResque::ACTION_META_KEY]['id'],
                    false,
                    $contentError,
                    false
                );
            }
        });
    }

    /**
     * Execute the job/action.
     */
    public function perform()
    {
        // Retrieve the job from the database mysql
        if ($this->_job === null) {
            $this->logError(
                LogJob::CATEGORY_RUNTIME_EXCEPTION,
                $this->args[YiiResque::ACTION_META_KEY]['id'],
                false,
                [
                    'message' => Yii::t('resque', 'Job not found in database mysql.')
                ],
                false
            );
        } else {
            // Retrieve the job from the database mongoDB
            $mongoJob = mongoJob::findOne(['_id' => $this->_job->id_mongo]);
            if ($mongoJob === null) {
                $this->logError(
                    LogJob::CATEGORY_RUNTIME_EXCEPTION,
                    $this->args[YiiResque::ACTION_META_KEY]['id'],
                    false,
                    [
                        'message' => Yii::t('resque', 'Job not found in database mongo.')
                    ],
                    false
                );
            } else {
                // Call the job
                if (isset($mongoJob['class']) && strlen($mongoJob['class']) > 0) {
                    $func = '\\' . $mongoJob['class'] . '::' . $mongoJob['action'];
                    try {
                        $this->result = call_user_func($func, $this->args['params']);
                        $this->result['class'] = $mongoJob['class'];
                        $this->result['action'] = $mongoJob['action'];
                    } catch (ErrorException $e) {
                        $this->result = [
                            'action' => $mongoJob['action'],
                            'class' => $mongoJob['class'],
                            'success' => false,
                            'message' => $e->getMessage(),
                            'executed_at' => time(),
                            'error' => $e
                        ];
                    } catch (\Exception $e) {
                        $this->result = [
                            'action' => $mongoJob['action'],
                            'class' => $mongoJob['class'],
                            'success' => false,
                            'message' => $e->getMessage(),
                            'executed_at' => time(),
                            'error' => $e
                        ];
                    }
                }
            }
        }
    }

    /**
     *
     */
    public function tearDown()
    {

    }
}