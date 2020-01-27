<?php

use yii\db\Migration;

/**
 * Class m190829_071114_add_ga_code_field_to_website_translation_table
 */
class m200127_183422_add_usersnap_to_website_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%website_translation}}', 'usersnap_code', $this->string(64)->after('google_tag_manager_code'));
        $this->addColumn('{{%website_translation}}', 'usersnap_type', $this->string(32)->after('usersnap_code')->defaultValue('disabled'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%website_translation}}', 'usersnap_code');
        $this->dropColumn('{{%website_translation}}', 'usersnap_type');
    }

}
