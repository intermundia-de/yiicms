<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181207_204200_alter_timeline_event_data_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('{{%timeline_event}}', 'data', 'LONGTEXT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('{{%timeline_event}}', 'data', $this->text());
    }
}
