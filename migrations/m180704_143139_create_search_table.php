<?php

use yii\db\Migration;

/**
 * Handles the creation of table `search`.
 */
class m180704_143139_create_search_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('search', [
            'id' => $this->primaryKey(),
            'content_tree_id' => $this->integer(),
            'table_name' => $this->string(),
            'record_id' => $this->integer(),
            'language' => $this->string(),
            'attribute' => $this->string(),
            'content' => 'LONGTEXT'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('search');
    }
}
