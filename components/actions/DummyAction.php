<?php

namespace spiritdead\resque\components\actions;

/**
 * Class DummyJob
 * @package common\components\job\actions
 */
class DummyAction extends BaseAction implements ActionInterface
{
    /**
     * @param array $args
     * @return array
     */
    public static function process($args = [])
    {
        return [
            'success' => true,
            'message' => 'Dummy action executed',
            'executed_at' => time()
        ];
    }
}