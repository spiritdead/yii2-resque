<?php

namespace spiritdead\yii2resque;

use spiritdead\yii2resque\traits\TranslationTrait;
use yii\base\Module as BaseModule;
use yii;
use yii\console\Application;
use yii\base\InvalidConfigException;

/**
 * resque module definition class
 */
class Module extends BaseModule implements yii\base\BootstrapInterface
{
    use TranslationTrait;

    /**
     * @var array The internalization configuration for this widget
     */
    public $i18n = [];

    /**
     * @var string Translation message file category name for i18n
     */
    protected $_msgCat = 'resque';
    /**
     * @var string The module name for Krajee gridview
     */
    const MODULE = "resque";

    /**
     * @var string
     */
    public $controllerNamespace = 'spiritdead\yii2resque\controllers';

    /**
     * @var string BaseAlias for the layouts
     */
    public $layoutAlias;

    /**
     * @inheritdoc
     */
    public function init()
    {
        //@todo: save works if the redis is gone, and create a command for register in the redis manually (add status for this in the job)
        if (!isset(Yii::$app->yiiResque)) {
            throw new InvalidConfigException("Please define the server and the port in the config of the component yiiResque");
        }
        if (isset($this->layout) && empty($this->layout)) {
            $this->layout = false;
        }
        parent::init();
        Yii::setAlias('@resque', dirname(dirname(__DIR__)));
        $this->initI18N();
        // Custom initialization code goes here
    }

    /**
     * Load custom layout
     * @return string
     */
    public function getLayoutPath()
    {
        if ($this->layoutAlias === null || empty($this->layoutAlias)) {
            return parent::getLayoutPath(); // TODO: Change the autogenerated stub
        }
        return Yii::getAlias('@' . $this->layoutAlias) . '\views\layouts';
    }

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $this->controllerNamespace = 'spiritdead\yii2resque\commands';
        }
    }
}
