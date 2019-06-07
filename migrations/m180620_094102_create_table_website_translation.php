<?php

use yii\db\Migration;

/**
 * Class m180620_094102_create_table_website_translation
 */
class m180620_094102_create_table_website_translation extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%website_translation}}', [
            'id' => $this->primaryKey(11),
            'website_id' => $this->integer(11),
            'language' => $this->string(15),
            'name' => $this->string(),
            'title' => $this->string(512),
            'og_site_name' => $this->string(),
            'address_of_company' => $this->text(),
            'cookie_disclaimer_message' => $this->text(),
            'short_description' => $this->text(),
            'logo_image_name' => $this->text(),
            'additional_logo_image_name' => $this->text(),
            'copyright' => $this->text(),
            'google_tag_manager_code' => $this->string(),
            'html_code_before_close_body' => 'LONGTEXT',
            'footer_name' => $this->string(),
            'footer_headline' => $this->string(),
            'footer_plain_text' => $this->text(),
            'footer_logo' => $this->string(),
            'footer_copyright' => $this->string(),
        ]);

        $this->addForeignKey('{{%FK_website_translation_website}}',
            '{{%website_translation}}',
            'website_id',
            '{{%website}}',
            'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%FK_website_translation_website}}','{{%website_translation}}');
        $this->dropTable('{{%website_translation}}');
    }

}
