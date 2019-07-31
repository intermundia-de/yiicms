<?php

use yii\db\Migration;

/**
 * Handles adding website_key to table `{{%timeline_event}}`.
 */
class m190726_132047_add_website_key_column_to_timeline_event_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\intermundia\yiicms\models\TimelineEvent::tableName(),
            'website_key', $this->string(1024)->null()->after('id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\intermundia\yiicms\models\TimelineEvent::tableName(),
            'website_key');
    }
}
