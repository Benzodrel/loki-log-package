<?php


namespace BoltSystem\Yii2Logs\log\event\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%event_log}}`.
 */
class m250226_103832_create_event_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable('{{%logs_event}}', [
            'id'          => $this->primaryKey()->comment('ID'),
            'type'        => $this->string(250)->notNull()->default('')->comment('Тип'),
            'status_id'   => $this->integer(6)->notNull()->default(0)->comment('Статус'),
            'meta'        => $this->json()->null()->comment('META'),
            'date_create' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%logs_event}}');
    }
}