<?php

use yii\db\Migration;

/**
 * Class m190807_144447_add_corporate_data_fields_to_website_translation_table
 */
class m190807_144447_add_corporate_data_fields_to_website_translation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'contact_type', $this->string(255)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'telephone', $this->string(255)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'social_links', $this->text()->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_city', $this->string(255)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_postal_code', $this->string(255)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_country', $this->string(255));
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'location_latitude', $this->decimal(8,6)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'location_longitude', $this->decimal(9,6)->null());
        $this->addColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_business_hours', $this->string(2048)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_business_hours');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'location_longitude');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'location_latitude');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_country');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_postal_code');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'company_city');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'social_links');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'telephone');
        $this->dropColumn(\intermundia\yiicms\models\WebsiteTranslation::tableName(), 'contact_type');
    }
}
