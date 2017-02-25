<?php

namespace spiritdead\yii2resque\components\base;

/**
 * Interface JobInterface
 * @package spiritdead\yii2resque\components\base
 */
interface JobInterface
{
    /**
     * @return mixed
     */
    public function perform();
}