<?php

use yii\db\Migration;

/**
 * Class m190725_083323_add_login_attempt_count_to_user_table
 */
class m190725_083323_add_login_attempt_count_to_user_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'login_attempt', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user}}', 'suspended_till', $this->integer()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'login_attempt');
        $this->dropColumn('{{%user}}', 'suspended_till');
    }
}
