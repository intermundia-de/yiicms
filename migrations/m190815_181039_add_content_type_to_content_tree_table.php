<?php

use common\models\ContentTree;
use yii\db\Migration;

/**
 * Class m190529_145739_add_admin_email_fields_to_website_translation
 */
class m190815_181039_add_content_type_to_content_tree_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%content_tree}}', 'content_type', $this->string(255)->after('table_name'));
        Yii::$app->db
            ->createCommand("UPDATE content_tree SET content_type = `table_name` WHERE content_type IS NULL")
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_tree}}', 'content_type');
    }
}
