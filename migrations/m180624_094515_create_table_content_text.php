<?php

use yii\db\Migration;

/**
 * Class m180624_094515_create_table_content_text
 */
class m180624_094515_create_table_content_text extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_text}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->integer(),
            'deleted_by' => $this->integer()
        ]);

        $this->addForeignKey(
            'FK_content_text_created_by',
            '{{%content_text}}',
            'created_by',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'FK_content_text_updated_by',
            '{{%content_text}}',
            'updated_by',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'FK_content_text_deleted_by',
            '{{%content_text}}',
            'deleted_by',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_content_text_created_by', '{{%content_text}}');
        $this->dropForeignKey('FK_content_text_updated_by', '{{%content_text}}');
        $this->dropForeignKey('FK_content_text_deleted_by', '{{%content_text}}');
        $this->dropTable('{{%content_text}}');
    }
}
