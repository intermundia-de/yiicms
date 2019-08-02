<?php

use yii\db\Migration;

/**
 * Handles adding show_in_sitemap to table `{{%content_tree}}`.
 */
class m190802_124024_add_include_in_sitemap_column_to_content_tree_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\intermundia\yiicms\models\ContentTree::tableName(), 'in_sitemap',
            $this->tinyInteger(1)->defaultValue(1)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\intermundia\yiicms\models\ContentTree::tableName(), 'in_sitemap');
    }
}
