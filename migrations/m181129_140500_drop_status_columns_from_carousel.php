<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181129_140500_drop_status_columns_from_carousel extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%carousel}}', 'status');
        $this->dropColumn('{{%carousel_translation}}', 'status');
        $this->dropColumn('{{%carousel_item}}', 'status');
        $this->dropColumn('{{%carousel_item_translation}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('{{%carousel}}', 'status', $this->integer(1));
        $this->addColumn('{{%carousel_translation}}', 'status', $this->integer(1));
        $this->addColumn('{{%carousel_item}}', 'status', $this->integer(1));
        $this->addColumn('{{%carousel_item_translation}}', 'status', $this->integer(1));
    }
}
