<?php

use yii\db\Migration;

/**
 * Class m180727_121932_add_blame_attributes_to_video_section
 */
class m181024_121110_add_multi_line2_to_content_text_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%content_text_translation}}', 'multi_line2', 'LONGTEXT');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%content_text_translation}}', 'multi_line2');
    }

}
