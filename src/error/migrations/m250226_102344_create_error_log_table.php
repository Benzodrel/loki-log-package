<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%error_log}}`.
 */
class m250226_102344_create_error_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable('{{%logs_error}}', [
            'id'          => $this->primaryKey()->comment('ID'),
            'level'       => $this->string(250)->notNull()->defaultValue('')->comment('Уровень'),
            'code'        => $this->string(250)->notNull()->defaultValue('')->comment('Код'),
            'description' => $this->text()->null()->comment('Описание'),
            'meta'        => $this->json()->null()->comment('META'),
            'user_id'     => $this->integer()->notNull()->defaultValue(0)->comment('Пользователь'),
            'date_create' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')->comment('Дата создания'),
            'url'         => $this->text()->null()->comment('URL страницы')
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%logs_error}}');
    }
}