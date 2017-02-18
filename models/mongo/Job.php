<?php

namespace spiritdead\resque\models\mongo;

use Yii;
use yii\mongodb\ActiveRecord;

/**
 *  This is the model class for table "job".
 *
 * Class Job
 * @package spiritdead\resque\models\mongo
 *
 * @property integer $_id
 * @property string $class
 * @property string $action
 * @property mixed $data
 */
class Job extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'job';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'action', 'data'], 'required'],
            [['class', 'action'], 'string', 'max' => 255],
            [['data'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'class',
            'action',
            'data'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => Yii::t('resque', 'ID'),
            'class' => Yii::t('resque', 'Class'),
            'action' => Yii::t('resque', 'Action'),
            'data' => Yii::t('resque', 'Data')
        ];
    }
}
