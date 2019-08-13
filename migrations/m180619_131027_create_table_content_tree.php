<?php

use yii\db\Migration;

/**
 * Class m180619_072027_create_table_content_tree
 */
class m180619_131027_create_table_content_tree extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_tree}}', [
            'id' => $this->primaryKey(),
            'record_id' => $this->integer()->notNull(),
            'link_id' => $this->integer(),
            'table_name' => $this->string(255)->notNull(),
            'lft' => $this->integer()->notNull(),
            'rgt' => $this->integer()->notNull(),
            'depth' => $this->integer()->notNull(),
            'created_at' => $this->bigInteger(),
            'created_by' => $this->integer(),
            'updated_at' => $this->bigInteger(),
            'updated_by' => $this->integer(),
            'deleted_at' => $this->bigInteger(),
            'deleted_by' => $this->integer(),
            'hide' => $this->boolean()->defaultValue(false),
            'view' => $this->string(64),
            'key' => $this->string(1024),
        ]);

        $this->addForeignKey('{{%FK_content_tree_id_link_id}}',
            '{{%content_tree}}',
            'link_id',
            '{{%content_tree}}',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_content_tree_id_parent_id}}', '{{%content_tree}}');
        $this->dropTable('{{%content_tree}}');
    }
}
