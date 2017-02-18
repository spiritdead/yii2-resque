<?php

namespace spiritdead\resque\components\base;

/**
 * Interface JobInterface
 * @package spiritdead\resque\components\base
 */
interface JobInterface
{
    /**
     * @return mixed
     */
    public function perform();
}