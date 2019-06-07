<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181219_172100_alter_content_tree_add_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%content_tree}}', 'key', $this->string(1024));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_tree}}', 'key');
    }
}
