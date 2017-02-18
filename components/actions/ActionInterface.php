<?php

namespace spiritdead\resque\components\actions;

/**
 * Interface JobInterface
 * @package common\components\job\actions
 */
interface ActionInterface
{
    /**
     * @param array $args
     * @return mixed
     */
    public static function process($args = []);
}