<?php

use yii\db\Migration;

/**
 * Class m180622_144501_add_insert__menu_items
 */
class m180729_184501_add_insert_menu_items extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%menu}}', [
            'id' => 1,
            'name' => 'Header',
            'key' => 'header',
        ]);


        // Insert page Home into "Header" menu
        $this->insert('{{%content_tree_menu}}', [
            'id' => 1,
            'content_tree_id' => 2,
            'menu_id' => 1,
            'position' => 1
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%menu}}', ['id' => [1]]);
    }

}
