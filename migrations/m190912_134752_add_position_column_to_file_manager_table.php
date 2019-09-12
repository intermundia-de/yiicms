<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%file_manager}}`.
 */
class m190912_134752_add_position_column_to_file_manager_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%file_manager_item}}', 'position', $this->integer()->after('record_id')->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%file_manager_item}}', 'position');
    }
}
