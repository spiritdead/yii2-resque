<?php

namespace spiritdead\resque\components\actions;

/**
 * Class DummyJob
 * @package spiritdead\resque\components\actions
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