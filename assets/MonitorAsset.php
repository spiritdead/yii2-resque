<?php

namespace spiritdead\yii2resque\assets;

use yii\web\AssetBundle;

/**
 * Class MonitorAsset
 * @package spiritdead\yii2resque\assets
 */
class MonitorAsset extends AssetBundle
{
    //public $basePath = __DIR__;
    public $sourcePath = '@resque/assets/';
    //public $baseUrl = '@web';
    /**
     * @inheritdoc
     */
    public function init(){
        //$this->setupAssets('css', ['css/kv-grid']);
        parent::init();
    }

    //public $basePath = '@webroot';
    //public $baseUrl = '@web';
    public $css = [
        'css/custom.css',
    ];
    public $js = [
        'js/custom.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'yii\web\JqueryAsset'
    ];
}