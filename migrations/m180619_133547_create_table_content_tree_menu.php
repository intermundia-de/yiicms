<?php

use yii\db\Migration;

/**
 * Class m180619_133547_create_table_content_tree_menu
 */
class m180619_133547_create_table_content_tree_menu extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_tree_menu}}', [
            'id' => $this->primaryKey(),
            'content_tree_id'=> $this->integer(),
            'menu_id' => $this->integer(),
            'position' => $this->integer()
        ]);

        $this->addForeignKey(
            'FK_content_tree_menu_content_tree',
            '{{%content_tree_menu}}',
            'content_tree_id',
            '{{%content_tree}}',
            'id'
        );
        $this->addForeignKey(
            'FK_content_tree_menu_menu',
            '{{%content_tree_menu}}',
            'menu_id',
            '{{%menu}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_content_tree_menu_content_tree}}','{{%content_tree_menu}}');
        $this->dropForeignKey('{{%FK_content_tree_menu_menu}}','{{%content_tree_menu}}');
        $this->dropTable('{{%content_tree_menu}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180619_133547_create_table_content_tree_menu cannot be reverted.\n";

        return false;
    }
    */
}
