<?php

use yii\db\Migration;

/**
 * Class m180620_094102_create_table_website_translation
 */
class m180725_114620_create_table_carousel_item_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%carousel_item_translation}}', [
            'id' => $this->primaryKey(11),
            'carousel_item_id' => $this->integer(11),
            'name' => $this->string(),
            'caption' => $this->text(),
            'status' => $this->smallInteger(),
            'language' => $this->string(25),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
        ]);

        $this->addForeignKey('{{%FK_carousel_item_translation_carousel_item}}',
            '{{%carousel_item_translation}}',
            'carousel_item_id',
            '{{%carousel_item}}',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_carousel_item_translation_carousel_item}}','{{%carousel_item_translation}}');
        $this->dropTable('{{%carousel_item_translation}}');
    }
}
