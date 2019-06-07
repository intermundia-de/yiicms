<?php

use yii\db\Migration;

/**
 * Class m180725_081237_create_table_country
 */
class m180725_081237_create_table_country extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable("{{%country}}", [
            'id' => $this->primaryKey(),
            'status' => $this->boolean(),
            'iso_code_1' => $this->string(3),
            'iso_code_2' => $this->string(3),
            'created_at' => $this->integer(11),
            'updated_at' => $this->integer(11),
            'deleted_at' => $this->integer(11),
            'created_by' => $this->integer(11),
            'updated_by' => $this->integer(11),
            'deleted_by' => $this->integer(11)
        ]);

        $this->addForeignKey(
            'country_user_created_by',
            '{{%country}}',
            'created_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'country_user_updated_by',
            '{{%country}}',
            'updated_by',
            '{{%user}}',
            'id',
            'NO ACTION'
        );
        $this->addForeignKey(
            'country_user_deleted_by',
            '{{%country}}',
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
        $this->dropForeignKey('country_user_created_by','{{%country}}');
        $this->dropForeignKey('country_user_updated_by','{{%country}}');
        $this->dropForeignKey('country_user_deleted_by','{{%country}}');
        $this->dropTable('{{%country}}');
    }

}
