<?php

namespace spiritdead\resque;

use yii\base\Module as BaseModule;

/**
 * resque module definition class
 */
class Module extends BaseModule
{
    /**
     * The module name for Krajee gridview
     */
    const MODULE = "resque";

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'spiritdead\resque\controllers';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
}
