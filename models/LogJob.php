<?php

namespace spiritdead\resque\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "log_job".
 *
 * @property integer $id
 * @property integer $job_id
 * @property boolean $success
 * @property integer $category
 * @property string $data
 * @property integer $event_time
 * @property boolean $new
 *
 * @property Job $job
 */
class LogJob extends \yii\db\ActiveRecord
{
    /**
     *
     */
    const CATEGORY_SUCCESS = 1;

    /**
     *
     */
    const CATEGORY_ERROR_DONTPERFORM = 2;

    /**
     *
     */
    const CATEGORY_RUNTIME_EXCEPTION = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'log_job';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['event_time'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [],
                ],
                // if you're using datetime instead of UNIX timestamp:
                //'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function afterFind()
    {
        $this->data = json_decode($this->data);
        return parent::afterFind();
    }

    public function beforeValidate()
    {
        if (!(is_string($this->data) && is_object(json_decode($this->data)) && (json_last_error() == JSON_ERROR_NONE))) {
            $this->data = json_encode($this->data);
        }
        return parent::beforeValidate();
    }

    public function afterSave($insert, $changedAttributed)
    {
        if (is_string($this->data)) {
            $this->data = json_decode($this->data);
        }
        return parent::afterSave($insert, $changedAttributed);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['job_id', 'category', 'event_time'], 'integer'],
            [['success', 'new'], 'boolean'],
            [['category', 'data'], 'required'],
            [['data'], 'string'],
            [['category'], 'string', 'max' => 64],
            [
                ['job_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Job::className(),
                'targetAttribute' => ['job_id' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('resque', 'ID'),
            'job_id' => Yii::t('resque', 'Job ID'),
            'success' => Yii::t('resque', 'Success'),
            'category' => Yii::t('resque', 'Category'),
            'data' => Yii::t('resque', 'Data'),
            'event_time' => Yii::t('resque', 'Event Time'),
            'new' => Yii::t('resque', 'New'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJob()
    {
        return $this->hasOne(Job::className(), ['id' => 'job_id']);
    }

    /**
     * @param int $category
     * @param int|null $job_id
     * @param boolean $success
     * @param boolean $data
     * @return boolean
     */
    public static function log($category, $job_id, $success, $data = false)
    {
        $model = new self;
        $model->job_id = $job_id;
        $model->success = $success;
        $model->category = $category;
        $model->data = $data;
        $model->new = true;
        $model->save();
        return true;
    }
}
