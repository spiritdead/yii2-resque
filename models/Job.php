<?php

namespace spiritdead\yii2resque\models;

use Yii;
use common\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "job".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $id_mongo
 * @property integer $created_at
 * @property integer $result
 * @property string $result_message
 * @property integer $executed_at
 * @property integer $scheduled
 * @property integer $scheduled_at
 *
 * @property User $user
 * @property LogJob[] $logJobs
 */
class Job extends \yii\db\ActiveRecord
{
    /**
     * job process successful
     */
    const RESULT_SUCCESS = 1;

    /**
     * job process failed
     */
    const RESULT_FAILED = 2;

    /**
     * job process in queue
     */
    const RESULT_NONE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'job';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => [],
                ],
                // if you're using datetime instead of UNIX timestamp:
                //'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'result', 'executed_at', 'scheduled', 'scheduled_at'], 'integer'],
            [['result_message'], 'string'],
            [['id_mongo'], 'string', 'max' => 255],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::className(),
                'targetAttribute' => ['user_id' => 'id']
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
            'user_id' => Yii::t('resque', 'User ID'),
            'id_mongo' => Yii::t('resque', 'Id Mongo'),
            'created_at' => Yii::t('resque', 'Created At'),
            'result' => Yii::t('resque', 'Result'),
            'result_message' => Yii::t('resque', 'Result Message'),
            'executed_at' => Yii::t('resque', 'Executed At'),
            'scheduled' => Yii::t('resque', 'Scheduled'),
            'scheduled_at' => Yii::t('resque', 'Scheduled At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogJobs()
    {
        return $this->hasMany(LogJob::className(), ['job_id' => 'id']);
    }
}
