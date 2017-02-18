<?php

namespace spiritdead\resque;

use spiritdead\resque\traits\TranslationTrait;
use yii\base\Module as BaseModule;
use yii;
use yii\base\Application;
use yii\base\InvalidConfigException;

/**
 * resque module definition class
 */
class Module extends BaseModule implements yii\base\BootstrapInterface
{
    use TranslationTrait;

    /**
     * @var array the the internalization configuration for this widget
     */
    public $i18n = [];

    /**
     * @var string translation message file category name for i18n
     */
    protected $_msgCat = 'resque';
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
        if(!isset(Yii::$app->yiiResque)) {
            throw new InvalidConfigException("Please define the server and the port in the config of the component yiiResque");
        }
        parent::init();
        Yii::setAlias('resque', dirname(dirname(__DIR__)));
        $this->initI18N();
        // custom initialization code goes here
    }

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = 'spiritdead\resque\commands';
        }
    }
}
