<?php

use yii\db\Migration;

/**
 * Class m180620_094102_create_table_website_translation
 */
class m180725_114520_create_table_carousel_item extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%carousel_item}}', [
            'id' => $this->primaryKey(11),
            'status' => $this->smallInteger(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_at' => $this->integer(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->integer(),
            'deleted_by' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%carousel_item}}');
    }
}
