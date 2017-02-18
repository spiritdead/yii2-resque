<?php

namespace spiritdead\resque\components\actions;

/**
 * Interface JobInterface
 * @package spiritdead\resque\components\actions
 */
interface ActionInterface
{
    /**
     * @param array $args
     * @return mixed
     */
    public static function process($args = []);
}