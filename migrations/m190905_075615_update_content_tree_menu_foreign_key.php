<?php

use yii\db\Migration;

/**
 * Class m190905_075615_update_content_tree_menu_foreign_key
 */
class m190905_075615_update_content_tree_menu_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey(
            'FK_content_tree_menu_content_tree',
            '{{%content_tree_menu}}'
        );
        $this->addForeignKey(
            'FK_content_tree_menu_content_tree',
            '{{%content_tree_menu}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id',
            'cascade'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'FK_content_tree_menu_content_tree',
            '{{%content_tree_menu}}'
        );
        $this->addForeignKey(
            'FK_content_tree_menu_content_tree',
            '{{%content_tree_menu}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id'
        );
    }

}
