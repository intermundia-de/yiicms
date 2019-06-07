<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181105_141151_add_lang_column_to_filemanager extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%file_manager_item}}', 'language', $this->string(12)->after('column_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%file_manager_item}}', 'language');
    }
}
