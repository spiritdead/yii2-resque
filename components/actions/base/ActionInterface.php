<?php

namespace spiritdead\yii2resque\components\actions\base;

/**
 * Interface JobInterface
 * @package spiritdead\yii2resque\components\actions\base
 */
interface ActionInterface
{
    /**
     * @param array $args
     * @return mixed
     */
    public static function process($args = []);
}