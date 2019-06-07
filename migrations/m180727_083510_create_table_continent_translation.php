<?php

use yii\db\Migration;

/**
 * Class m180727_083510_create_table_continent_translation
 */
class m180727_083510_create_table_continent_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%continent_translation}}',[
            'id' => $this->primaryKey(),
            'continent_id' => $this->integer(11)->notNull(),
            'language' => $this->string(12)->notNull(),
            'name' => $this->string(512),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
            'deleted_at' => $this->integer(11),
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),
            'deleted_by' => $this->integer(11)
        ]);

        $this->addForeignKey(
            'fk_continent_translation_continent',
            '{{%continent_translation}}',
            'continent_id',
            '{{%continent}}',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'continent_translation_user_created_by',
            '{{%continent_translation}}',
            'created_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'continent_translation_user_updated_by',
            '{{%continent_translation}}',
            'updated_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'continent_translation_user_deleted_by',
            '{{%continent_translation}}',
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
        $this->dropForeignKey('continent_translation_user_created_by','{{%country}}');
        $this->dropForeignKey('continent_translation_user_updated_by','{{%country}}');
        $this->dropForeignKey('continent_translation_user_deleted_by','{{%country}}');
        $this->dropForeignKey('fk_continent_translation_continent','{{%continent_translation}}');
        $this->dropTable('{{%continent_translation}}');
    }
}
