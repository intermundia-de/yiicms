<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181128_161413_alter_content_tree_translation_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('FK_content_tree_translation_content_tree', '{{%content_tree_translation}}');
        $this->addForeignKey(
            'FK_content_tree_translation_content_tree',
            '{{%content_tree_translation}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_content_tree_translation_content_tree', '{{%content_tree_translation}}');
        $this->addForeignKey(
            'FK_content_tree_translation_content_tree',
            '{{%content_tree_translation}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id'
        );
    }
}
