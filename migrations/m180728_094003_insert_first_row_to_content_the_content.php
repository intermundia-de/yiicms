<?php

use yii\db\Migration;

/**
 * Class m180622_094103_insert_first_row_to_content_the_content
 */
class m180728_094003_insert_first_row_to_content_the_content extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%website}}', [
            'id' => 1,
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1,
        ]);

        $this->insert('{{%website_translation}}', [
            'id' => 1,
            'language' => Yii::$app->language,
            'website_id' => 1
        ]);

        $this->insert('{{%content_tree}}', [
            'id' => 1,
            'record_id' => 1,
            'table_name' => 'website',
            'depth' => 0,
            'lft' => 1,
            'rgt' => 4,
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1,
        ]);

        $this->insert('{{%content_tree_translation}}', [
            'id' => 1,
            'language' => Yii::$app->language,
            'content_tree_id' => 1,
            'alias' => 'website',
            'alias_path' => 'website',
            'name' => 'Website'
        ]);


        // ===================================

        $this->insert('{{%page}}', [
            'id' => 1,
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1,
        ]);

        $this->insert('{{%page_translation}}', [
            'id' => 1,
            'page_id' => 1,
            'language' => Yii::$app->language,
            'title' => 'Home',
        ]);

        $this->insert('{{%content_tree}}', [
            'id' => 2,
            'record_id' => 1,
            'table_name' => 'page',
            'depth' => 1,
            'lft' => 2,
            'rgt' => 3,
            'created_at' => time(),
            'created_by' => 1,
            'updated_at' => time(),
            'updated_by' => 1,
        ]);

        $this->insert('{{%content_tree_translation}}', [
            'id' => 2,
            'language' => Yii::$app->language,
            'content_tree_id' => 2,
            'alias' => 'home',
            'alias_path' => 'home',
            'name' => 'Home'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%content_tree_translation}}', ['id' => [1, 2]]);
        $this->delete('{{%content_tree}}', ['id' => [1, 2]]);
        $this->delete('{{%page_translation}}', ['id' => 1]);
        $this->delete('{{%page}}', ['id' => 1]);
        $this->delete('{{%website_translation}}', ['id' => 1]);
        $this->delete('{{%website}}', ['id' => 1]);
    }
}
