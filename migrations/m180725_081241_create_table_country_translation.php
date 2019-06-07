<?php

use yii\db\Migration;

/**
 * Class m180725_081241_create_table_country_translation
 */
class m180725_081241_create_table_country_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%country_translation}}', [
            'id' => $this->primaryKey(),
            'country_id' => $this->integer(11)->notNull(),
            'language' => $this->string(12)->notNull(),
            'name' => $this->string(1024),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
            'deleted_at' => $this->integer(11),
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),
            'deleted_by' => $this->integer(11)
        ]);

        $this->addForeignKey(
            'country_country_translation',
            '{{%country_translation}}',
            'country_id',
            '{{%country}}',
            'id',
            'NO ACTION'
        );

        $this->addForeignKey(
            'country_translation_user_created_by',
            '{{%country_translation}}',
            'created_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'country_translation_user_updated_by',
            '{{%country_translation}}',
            'updated_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'country_translation_user_deleted_by',
            '{{%country_translation}}',
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
        $this->dropForeignKey('country_country_translation','{{%country_translation}}');
        $this->dropForeignKey('country_translation_user_created_by','{{%country_translation}}');
        $this->dropForeignKey('country_translation_user_updated_by','{{%country_translation}}');
        $this->dropForeignKey('country_translation_user_deleted_by','{{%country_translation}}');
        $this->dropTable('{{%country_translation}}');
    }
}
