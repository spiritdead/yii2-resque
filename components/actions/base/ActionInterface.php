<?php

namespace spiritdead\resque\components\actions\base;

/**
 * Interface JobInterface
 * @package spiritdead\resque\components\actions\base
 */
interface ActionInterface
{
    /**
     * @param array $args
     * @return mixed
     */
    public static function process($args = []);
}