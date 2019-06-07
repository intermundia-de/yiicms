<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m190227_203600_add_website_attribute_to_content_tree_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%content_tree}}', 'website', $this->integer()->after('table_name'));
        $website = \intermundia\yiicms\models\ContentTree::findClean()->orderBy('id')->limit(1)->one();
        \intermundia\yiicms\models\ContentTree::updateAll(['website' => $website->id], 'lft >= :lft AND rgt <= :rgt',
            ['lft' => $website->lft, 'rgt' => $website->rgt]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_tree}}', 'website');
    }
}
