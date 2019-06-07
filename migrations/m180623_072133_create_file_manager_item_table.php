<?php

use yii\db\Migration;

/**
 * Handles the creation of table `file_manager_item`.
 */
class m180623_072133_create_file_manager_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%file_manager_item}}', [
            'id' => $this->primaryKey(),
            'table_name' => $this->string(),
            'column_name' => $this->string(),
            'record_id' => $this->integer(),
            'base_url' => $this->text(),
            'path' => $this->string(2000),
            'type' => $this->string(55),
            'mime' => $this->string(55),
            'size' => $this->integer(20),
            'name' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%file_manager_item}}');
    }
}
