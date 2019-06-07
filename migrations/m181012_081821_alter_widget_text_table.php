<?php

use yii\db\Migration;

/**
 * Class m181012_081821_alter_widget_text_table
 */
class m181012_081821_alter_widget_text_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn(\intermundia\yiicms\models\WidgetText::tableName(), 'title');
        $this->dropColumn(\intermundia\yiicms\models\WidgetText::tableName(), 'body');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn(\intermundia\yiicms\models\WidgetText::tableName(), 'body', 'LONGTEXT');
        $this->addColumn(\intermundia\yiicms\models\WidgetText::tableName(), 'title', $this->string());
    }


}
