<?php

use yii\db\Migration;

/**
 * Class m180727_084504_continent_initials_insertions
 */
class m180727_084504_continent_initials_insertions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%continent}}', ['id' => '1',  'code' => 'AF','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '2',  'code' => 'AN','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '3',  'code' => 'AS','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '4',  'code' => 'EU','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '5',  'code' => 'NA','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '6',  'code' => 'OC','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);
        $this->insert('{{%continent}}', ['id' => '7',  'code' => 'SA','created_at' => time(),'updated_at' => time(),'created_by' => 1,'updated_by' => 1]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%continent}}');
    }
}
