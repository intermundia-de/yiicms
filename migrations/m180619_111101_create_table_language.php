<?php

use yii\db\Migration;

/**
 * Class m180619_111101_create_table_language
 */
class m180619_111101_create_table_language extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%language}}', [
            'code' => $this->string(12)->notNull()->unique(),
            'name' => $this->string(128),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->integer(),
            'deleted_by' => $this->integer()
        ]);

        $this->addPrimaryKey(
            'PK_language_primary_key',
            '{{%language}}',
            'code'
        );

        $this->addForeignKey(
            'FK_language_created_by',
            '{{%language}}',
            'created_by',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'FK_language_updated_by',
            '{{%language}}',
            'updated_by',
            '{{%user}}',
            'id'
        );

        $this->addForeignKey(
            'FK_language_deleted_by',
            '{{%language}}',
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

        $this->dropPrimaryKey('PK_language_primary_key', '{{%language}}');
        $this->dropForeignKey('FK_language_created_by', '{{%language}}');

        $this->dropForeignKey('FK_language_updated_by', '{{%language}}');

        $this->dropForeignKey('FK_language_deleted_by', '{{%language}}');
        $this->dropTable('{{%language}}');
    }
}
