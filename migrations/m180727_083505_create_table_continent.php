<?php

use yii\db\Migration;

/**
 * Class m180727_083505_create_table_continent
 */
class m180727_083505_create_table_continent extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("{{%continent}}",[
            'id' => $this->primaryKey(),
            'code' => $this->string(3),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
            'deleted_at' => $this->integer(11),
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),
            'deleted_by' => $this->integer(11)
        ]);

        $this->addForeignKey(
            'continent_user_created_by',
            '{{%continent}}',
            'created_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'continent_user_updated_by',
            '{{%continent}}',
            'updated_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'continent_user_deleted_by',
            '{{%continent}}',
            'deleted_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('continent_user_created_by','{{%country}}');
        $this->dropForeignKey('continent_user_updated_by','{{%country}}');
        $this->dropForeignKey('continent_user_deleted_by','{{%country}}');
        $this->dropTable('{{%continent}}');
    }
}
