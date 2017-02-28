<?php

namespace spiritdead\yii2resque\components\base;

use spiritdead\resque\exceptions\base\ResqueException;
use spiritdead\resque\exceptions\ResqueJobForceExitException;
use spiritdead\yii2resque\models\LogJob;
use spiritdead\yii2resque\models\Job as modelJob;

/**
 * Class Job
 * @package spiritdead\yii2resque\components\base
 */
class Job
{
    /**
     * @var array
     */
    public $args = [];

    /**
     * @var null
     */
    public $result = null;

    /**
     * @var null|modelJob
     */
    public $_job = null;

    /**
     * @param $category
     * @param string $error
     * @param bool $raise
     * @param array $throw
     * @throws ResqueException
     * @throws ResqueJobForceExitException
     */
    public function logError($category, $job_id, $success, $data, $raise = true, $throw = [])
    {
        // Log the error
        LogJob::log($category, $job_id, $success, $data);
        // Throw the exception
        if ($raise) {
            switch ($category) {
                case LogJob::CATEGORY_ERROR_DONTPERFORM:
                    throw new ResqueJobForceExitException();
                case LogJob::CATEGORY_RUNTIME_EXCEPTION:
                    throw new ResqueException();
            }
        }
    }

    /**
     * @param $category
     * @param $job_id
     * @param $success
     * @param $data
     */
    public function logResult($category, $job_id, $success, $data)
    {
        // Log the result
        LogJob::log($category, $job_id, $success, $data);
    }
}