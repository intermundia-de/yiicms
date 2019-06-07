<?php

use yii\db\Migration;

/**
 * Class m180624_095244_create_table_content_text_translation
 */
class m180624_095244_create_table_content_text_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_text_translation}}', [
            'id' => $this->primaryKey(),
            'content_text_id' => $this->integer(),
            'language' => $this->string(12),
            'name' => $this->string(1024),
            'single_line' => $this->string(2048),
            'multi_line' => 'LONGTEXT'
        ]);

        $this->addForeignKey(
            'FK_content_text_translation_content_text_id',
            '{{%content_text_translation}}',
            'content_text_id',
            '{{%content_text}}',
            'id'
        );

        $this->addForeignKey(
            'FK_content_text_translation_language',
            '{{%content_text_translation}}',
            'language',
            '{{%language}}',
            'code'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_content_text_translation_content_text_id', '{{%content_text_translation}}');

        $this->dropForeignKey('FK_content_text_translation_language', '{{%content_text_translation}}');
        $this->dropTable('{{%content_text_translation}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180624_095244_create_table_content_text_translation cannot be reverted.\n";

        return false;
    }
    */
}
