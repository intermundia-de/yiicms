<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m190222_123800_alter_page_translation_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('{{%FK_page_translation_page}}', '{{%page_translation}}');
        $this->addForeignKey('{{%FK_page_translation_page}}',
            '{{%page_translation}}',
            'page_id',
            '{{%page}}',
            'id', 'cascade', 'cascade');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_page_translation_page}}', '{{%page_translation}}');
        $this->addForeignKey('{{%FK_page_translation_page}}',
            '{{%page_translation}}',
            'page_id',
            '{{%page}}',
            'id');
    }
}
