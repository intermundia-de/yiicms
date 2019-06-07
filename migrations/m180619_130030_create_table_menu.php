<?php

use yii\db\Migration;

/**
 * Class m180619_133530_create_table_menu
 */
class m180619_130030_create_table_menu extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%menu}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'key' => $this->string()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%menu}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180619_133530_create_table_menu cannot be reverted.\n";

        return false;
    }
    */
}
