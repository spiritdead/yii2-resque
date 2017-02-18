<?php

namespace spiritdead\resque\components\actions;

/**
 * Class DummyJob
 * @package common\components\job\actions
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