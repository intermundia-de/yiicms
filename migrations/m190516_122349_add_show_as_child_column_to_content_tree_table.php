<?php

use yii\db\Migration;

/**
 * Handles adding show_as_child to table `content_tree`.
 */
class m190516_122349_add_show_as_child_column_to_content_tree_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%content_tree}}','show_as_sibling',$this->boolean()->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_tree}}','show_as_sibling');
    }
}
