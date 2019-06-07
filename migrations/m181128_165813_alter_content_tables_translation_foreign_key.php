<?php

use yii\db\Migration;

/**
 * Class m181105_141151_add_lang_column_to_filemanager
 */
class m181128_165813_alter_content_tables_translation_foreign_key extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('FK_video_section_video_section_translation', '{{%video_section_translation}}');
        $this->addForeignKey('FK_video_section_video_section_translation',
            '{{%video_section_translation}}',
            'video_section_id',
            '{{%video_section}}',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey('FK_content_text_translation_content_text_id', '{{%content_text_translation}}');
        $this->addForeignKey(
            'FK_content_text_translation_content_text_id',
            '{{%content_text_translation}}',
            'content_text_id',
            '{{%content_text}}',
            'id',
            'CASCADE'
        );
        $this->dropForeignKey('{{%FK_carousel_translation_carousel}}', '{{%carousel_translation}}');
        $this->addForeignKey('FK_carousel_translation_carousel',
            '{{%carousel_translation}}',
            'carousel_id',
            '{{%carousel}}',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey('{{%FK_carousel_item_translation_carousel_item}}', '{{%carousel_item_translation}}');
        $this->addForeignKey('FK_carousel_item_translation_carousel_item',
            '{{%carousel_item_translation}}',
            'carousel_item_id',
            '{{%carousel_item}}',
            'id',
            'CASCADE'
        );


        $this->dropForeignKey('FK_section_section_translation', '{{%section_translation}}');
        $this->addForeignKey(
            'FK_section_section_translation',
            '{{%section_translation}}',
            'section_id',
            '{{%section}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('FK_video_section_video_section_translation', '{{%video_section_translation}}');
        $this->addForeignKey('FK_video_section_video_section_translation',
            '{{%video_section_translation}}',
            'video_section_id',
            '{{%video_section}}',
            'id'
        );
        $this->dropForeignKey('FK_content_text_translation_content_text_id', '{{%content_text_translation}}');
        $this->addForeignKey(
            'FK_content_text_translation_content_text_id',
            '{{%content_text_translation}}',
            'content_text_id',
            '{{%content_text}}',
            'id'
        );

        $this->dropForeignKey('FK_carousel_translation_carousel', '{{%carousel_translation}}');
        $this->addForeignKey('{{%FK_carousel_translation_carousel}}',
            '{{%carousel_translation}}',
            'carousel_id',
            '{{%carousel}}',
            'id',
            'CASCADE'
        );

        $this->dropForeignKey('FK_carousel_item_translation_carousel_item', '{{%carousel_item_translation}}');
        $this->addForeignKey('{{%FK_carousel_item_translation_carousel_item}}',
            '{{%carousel_item_translation}}',
            'carousel_item_id',
            '{{%carousel_item}}',
            'id'
        );

        $this->dropForeignKey('FK_section_section_translation', '{{%section_translation}}');
        $this->addForeignKey(
            'FK_section_section_translation',
            '{{%section_translation}}',
            'section_id',
            '{{%section}}',
            'id'
        );
    }
}
