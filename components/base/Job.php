<?php

namespace spiritdead\resque\components\base;

use common\models\LogJob;
use common\models\Job as modelJob;
use Resque_Job_DontPerform;
use Resque_Exception;

/**
 * Class Job
 * @package common\components
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
     * @throws Resque_Exception
     * @throws Resque_Job_DontPerform
     */
    public function logError($category, $job_id, $success, $data, $raise = true, $throw = [])
    {
        // Log the error
        LogJob::log($category, $job_id, $success, $data);
        // Throw the exception
        if ($raise) {
            switch ($category) {
                case LogJob::CATEGORY_ERROR_DONTPERFORM:
                    throw new Resque_Job_DontPerform();
                case LogJob::CATEGORY_RUNTIME_EXCEPTION:
                    throw new Resque_Exception();
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