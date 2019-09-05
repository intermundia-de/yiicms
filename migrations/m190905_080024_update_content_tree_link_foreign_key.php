<?php

use yii\db\Migration;

/**
 * Class m190905_080024_update_content_tree_link_foreign_key
 */
class m190905_080024_update_content_tree_link_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('FK_content_tree_id_link_id',
            '{{%content_tree}}'
        );
        $this->addForeignKey('FK_content_tree_id_link_id',
            '{{%content_tree}}',
            'link_id',
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

        $this->dropForeignKey('FK_content_tree_id_link_id',
            '{{%content_tree}}'
        );
        $this->addForeignKey('FK_content_tree_id_link_id',
            '{{%content_tree}}',
            'link_id',
            '{{%content_tree}}',
            'id'
        );
    }
}
