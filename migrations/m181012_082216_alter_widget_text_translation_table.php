<?php

use yii\db\Migration;

/**
 * Class m181012_082216_alter_widget_text_translation_table
 */
class m181012_082216_alter_widget_text_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\intermundia\yiicms\models\WidgetTextTranslation::tableName(), 'short_description', 'LONGTEXT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\intermundia\yiicms\models\WidgetTextTranslation::tableName(), 'short_description');
    }


}
