<?php

use yii\db\Migration;

/**
 * Class m180620_094102_create_table_website_translation
 */
class m180725_113320_create_table_carousel_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%carousel_translation}}', [
            'id' => $this->primaryKey(11),
            'carousel_id' => $this->integer(11),
            'name' => $this->string(),
            'status' => $this->smallInteger(),
            'language' => $this->string(25),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer()
        ]);

        $this->addForeignKey('{{%FK_carousel_translation_carousel}}',
            '{{%carousel_translation}}',
            'carousel_id',
            '{{%carousel}}',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_carousel_translation_carousel}}','{{%carousel_translation}}');
        $this->dropTable('{{%carousel_translation}}');
    }
}
