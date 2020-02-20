<?php

use yii\db\Migration;

/**
 * Class m200220_190852_update_content_type_of_website_content_tree
 */
class m200220_190852_update_content_type_of_website_content_tree extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->update(\common\models\ContentTree::tableName(),
            ['content_type' => \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE],
            ['table_name' => \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE, 'content_type' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->update(\common\models\ContentTree::tableName(),
            ['content_type' => null],
            ['table_name' => \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE]);
    }
}
