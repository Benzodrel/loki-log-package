<?php

/**
 * Handles the creation of table `{{%log}}`.
 */
use yii\db\Migration;
class m250226_103244_create_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable('{{%logs_action}}', [
            'id'          => $this->primaryKey()->comment('ID'),
            'user_id'     => $this->integer()->notNull()->defaultValue(0)->comment('ID пользователя'),
            'type_id'     => $this->integer()->notNull()->defaultValue(0)->comment('ID типа'),
            'action_id'   => $this->integer()->notNull()->defaultValue(0)->comment('ID действия'),
            'entity_id'   => $this->integer()->notNull()->defaultValue(0)->comment('ID сущности'),
            'meta'        => $this->json()->notNull()->defaultValue('{}')->comment('META'),
            'date_create' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%logs_action}}');
    }
}