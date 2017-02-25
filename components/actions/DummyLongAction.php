<?php

namespace spiritdead\resque\components\actions;

use spiritdead\resque\components\actions\base\ActionInterface;
use spiritdead\resque\components\actions\base\BaseAction;


/**
 * Class DummyJob
 * @package spiritdead\resque\components\actions
 */
class DummyLongAction extends BaseAction implements ActionInterface
{
    /**
     * @param array $args
     * @return array
     */
    public static function process($args = [])
    {
        sleep($args['duration']);
        return [
            'success' => true,
            'message' => 'Dummy long action executed',
            'executed_at' => time()
        ];
    }
}