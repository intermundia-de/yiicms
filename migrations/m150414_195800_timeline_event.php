<?php

use yii\db\Migration;
use yii\helpers\Json;

class m150414_195800_timeline_event extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->createTable('{{%timeline_event}}', [
            'id' => $this->primaryKey(),
            'application' => $this->string(64)->notNull(),
            'group' => $this->string(),
            'category' => $this->string(64)->notNull(),
            'event' => $this->string(64)->notNull(),
            'record_id' => $this->integer(),
            'record_name' => $this->string(),
            'data' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()
        ]);

        $this->addForeignKey(
            'FK_timeline_event_created_by',
            '{{%timeline_event}}',
            'created_by',
            '{{%user}}',
            'id'
        );

        $this->createIndex('idx_created_at', '{{%timeline_event}}', 'created_at');

        $this->batchInsert(
            '{{%timeline_event}}',
            ['application', 'category', 'event', 'data', 'created_at'],
            [
                ['frontend', 'user', 'signup', Json::encode(['public_identity' => 'webmaster', 'user_id' => 1, 'created_at' => time()]), time()],
                ['frontend', 'user', 'signup', Json::encode(['public_identity' => 'manager', 'user_id' => 2, 'created_at' => time()]), time()],
                ['frontend', 'user', 'signup', Json::encode(['public_identity' => 'user', 'user_id' => 3, 'created_at' => time()]), time()]
            ]
        );
    }

    /**
     * @return bool|void
     */
    public function down()
    {
        $this->dropForeignKey('FK_timeline_event_created_by', '{{%timeline_event}}');
        $this->dropIndex('idx_created_at', '{{%timeline_event}}');
        $this->dropTable('{{%timeline_event}}');
    }
}
