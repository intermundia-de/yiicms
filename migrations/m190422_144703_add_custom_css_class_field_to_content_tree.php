<?php

use yii\db\Migration;

/**
 * Class m190422_144703_add_custom_style_field_to_teaser_translation
 */
class m190422_144703_add_custom_css_class_field_to_content_tree extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%content_tree}}','custom_class',$this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_tree}}','custom_class');
    }
}
