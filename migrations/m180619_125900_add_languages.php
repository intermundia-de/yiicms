<?php

use yii\db\Migration;

/**
 * Class m180619_125900_add_languages
 */
class m180619_125900_add_languages extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->insert('{{%language}}', [
            'code' => 'en-US',
            'name' => 'English',
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1
        ]);

        $this->insert('{{%language}}', [
            'code' => 'de-DE',
            'name' => 'German',
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->delete('{{%language}}', ['code' => ['en', 'de']]);
    }
}
