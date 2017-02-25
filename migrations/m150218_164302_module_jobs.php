<?php

namespace spiritdead\yii2resque\migrations;

use yii\db\Schema;
use yii\db\Migration;

class m150218_164302_module_jobs extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        } elseif ($this->db->driverName === 'pgsql') {
            $this->db->emulatePrepare = false;
        }

        $this->createTable('job', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER,
            'id_mongo' => Schema::TYPE_STRING,
            'created_at' => Schema::TYPE_INTEGER . '(11) NOT NULL',
            'result' => Schema::TYPE_INTEGER,
            'result_message' => Schema::TYPE_TEXT,
            'executed_at' => Schema::TYPE_INTEGER . '(11)',
            'scheduled' => Schema::TYPE_BOOLEAN . ' DEFAULT false',
            'scheduled_at' => Schema::TYPE_BOOLEAN . '(11)',
        ], $tableOptions);

        $this->createTable('log_job', [
            'id' => Schema::TYPE_PK,
            'job_id' => Schema::TYPE_INTEGER,
            'success' => Schema::TYPE_BOOLEAN . ' DEFAULT false',
            'category' => Schema::TYPE_INTEGER . ' NOT NULL',
            'data' => Schema::TYPE_TEXT . ' NOT NULL',
            'event_time' => Schema::TYPE_INTEGER . ' NOT NULL',
            'new' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT true',
        ], $tableOptions);

        //job indexs and foreign keys
        $this->createIndex('fk_job_user_idx', 'job', 'user_id');
        $this->addForeignKey('fk_job_user', 'job', 'user_id', 'user', 'id', 'CASCADE', 'NO ACTION');

        //log_job indexs and foreign keys
        $this->createIndex('fk_log-job_job_idx', 'log_job', 'job_id');
        $this->addForeignKey('fk_log-job_job', 'log_job', 'job_id', 'job', 'id', 'CASCADE', 'NO ACTION');
    }

    public function down()
    {
        $this->dropForeignKey('fk_job_user', 'job');
        $this->dropForeignKey('fk_log-job_job', 'log_job');
        $this->dropTable('job');
        $this->dropTable('log_job');
    }
}
