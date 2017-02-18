<?php

namespace spiritdead\resque\components\base;

/**
 * Interface JobInterface
 * @package common\components
 */
interface JobInterface
{
    /**
     * @return mixed
     */
    public function perform();
}