<?php

namespace spiritdead\yii2resque\components\actions;

use spiritdead\yii2resque\components\actions\base\ActionInterface;
use spiritdead\yii2resque\components\actions\base\BaseAction;

/**
 * Class DummyJob
 * @package spiritdead\yii2resque\components\actions
 */
class DummyErrorAction extends BaseAction implements ActionInterface
{
    /**
     * @param array $args
     * @return array
     */
    public static function process($args = [])
    {
        return [
            'success' => false,
            'message' => 'Dummy action error',
            'executed_at' => time()
        ];
    }
}