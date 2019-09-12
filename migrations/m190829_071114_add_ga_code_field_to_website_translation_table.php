<?php

use yii\db\Migration;

/**
 * Class m190829_071114_add_ga_code_field_to_website_translation_table
 */
class m190829_071114_add_ga_code_field_to_website_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('{{%website_translation}}', 'google_tag_manager_code', 'ga_code');
        $this->addColumn('{{%website_translation}}', 'google_tag_manager_code', $this->string(255)->after('ga_code'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%website_translation}}', 'google_tag_manager_code');
        $this->renameColumn('{{%website_translation}}', 'ga_code', 'google_tag_manager_code');
    }

}
