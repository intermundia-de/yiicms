<?php

use yii\db\Migration;

/**
 * Handles the creation of table `section`.
 */
class m180730_184501_create_section_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%section}}', [
            'id' => $this->primaryKey(),
            'deleted_at' => $this->integer(),
            'deleted_by' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer()
        ]);

        $this->createTable('{{%section_translation}}', [
            'id' => $this->primaryKey(),
            'section_id' => $this->integer(),
            'language' => $this->string(55),
            'template' => 'LONGTEXT',
            'title' => $this->string(2000),
            'description' => 'LONGTEXT',
        ]);

        $this->addForeignKey('FK_section_translation_language', '{{%section_translation}}', 'language', '{{%language}}', 'code');
        $this->addForeignKey('FK_section_section_translation', '{{%section_translation}}', 'section_id', '{{%section}}', 'id');
        $this->addForeignKey(
            'FK_section_deleted_by',
            '{{%section}}',
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
        $this->dropForeignKey('FK_section_deleted_by', '{{%section}}');
        $this->dropTable('{{%section}}');
    }
}
