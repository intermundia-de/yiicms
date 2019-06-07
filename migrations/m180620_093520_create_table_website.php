<?php

use yii\db\Migration;

/**
 * Class m180620_093520_create_table_website
 */
class m180620_093520_create_table_website extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%website}}', [
            'id' => $this->primaryKey(11),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->bigInteger(),
            'deleted_by' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%website}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180620_093520_create_table_website cannot be reverted.\n";

        return false;
    }
    */
}
