<?php

use yii\db\Migration;

/**
 * Handles the creation of table `video_section`.
 */
class m180622_141736_create_video_section_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%video_section}}', [
            'id' => $this->primaryKey(),
            'deleted_at' => $this->integer(),
            'deleted_by' => $this->integer(),
        ]);

        $this->createTable('{{%video_section_translation}}', [
            'id' => $this->primaryKey(),
            'language' => $this->string(12),
            'video_section_id' => $this->integer(),
            'title' => $this->string(255),
            'file' => $this->string(1000),
            'content_top' => $this->text(),
            'content_bottom' => $this->text()
        ]);

        $this->addForeignKey('FK_video_section_translation_language', '{{%video_section_translation}}', 'language', '{{%language}}', 'code');
        $this->addForeignKey('FK_video_section_video_section_translation', '{{%video_section_translation}}', 'video_section_id', '{{%video_section}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropForeignKey('FK_video_section_video_section_translation', '{{%video_section_translation}}');
        $this->dropForeignKey('FK_video_section_translation_language', '{{%video_section_translation}}');
        $this->dropTable('{{%video_section_translation}}');
        $this->dropTable('{{%video_section}}');
    }
}
