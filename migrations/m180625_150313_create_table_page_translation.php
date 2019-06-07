<?php

use yii\db\Migration;

/**
 * Class m180619_150313_create_table_page_translation
 */
class m180625_150313_create_table_page_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%page_translation}}', [
            'id' => $this->primaryKey(11),
            'page_id' => $this->integer(11)->notNull(),
            'language' => $this->string(15)->notNull(),
            'title' => $this->string(512)->notNull(),
            'short_description' => $this->text(),
            'body' => $this->text(),
            'meta_title' => $this->string(512),
            'meta_keywords' => $this->string(512),
            'meta_description' => $this->string(512),
        ]);

       $this->addForeignKey('{{%FK_page_translation_page}}',
           '{{%page_translation}}',
           'page_id',
           '{{%page}}',
           'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_page_translation_page}}','{{%page_translation}}');
        $this->dropTable('{{%page_translation}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180619_150313_create_table_page_translation cannot be reverted.\n";

        return false;
    }
    */
}
