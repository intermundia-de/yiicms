<?php

use yii\db\Migration;

/**
 * Class m180619_073504_create_table_content_tree_translation
 */
class m180619_131504_create_table_content_tree_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_tree_translation}}', [
            'id' => $this->primaryKey(),
            'content_tree_id' => $this->integer()->notNull(),
            'language' => $this->string(12)->notNull(),
            'name' => $this->string(255),
            'short_description' => $this->string(1024),
            'alias' => $this->string(255),
            'alias_path' => $this->string(2048)
        ]);

        $this->addForeignKey(
            'FK_content_tree_translation_content_tree',
            '{{%content_tree_translation}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id'
        );

        $this->addForeignKey(
            'FK_content_tree_translation_language',
            '{{%content_tree_translation}}',
            'language',
            '{{%language}}',
            'code'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_content_tree_translation_language', '{{%content_tree_translation}}');
        $this->dropForeignKey('FK_content_tree_translation_content_tree', '{{%content_tree_translation}}');
        $this->dropTable('{{%content_tree_translation}}');
    }
}
