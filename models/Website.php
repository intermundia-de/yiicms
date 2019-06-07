<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\WebsiteQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "website".
 *
 * @property int $id
 * @property string $logo_image
 * @property string $additional_logo_image
 * @property string $copyright
 * @property string $google_tag_manager_code
 * @property string $google_tag_manager_id
 * @property string $author
 * @property int $show_on_all_pages
 * @property string $claim_image
 * @property string $html_code_before_close_body
 * @property int $created_at
 * @property int $updated_at
 *
 * @property WebsiteTranslation[] $translations
 * @property WebsiteTranslation $activeTranslation
 */
class Website extends BaseModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%website}}';

    }

    /**
     * @return WebsiteQuery
     */
    public static function find()
    {
        return new WebsiteQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }


    public function getTitle()
    {
        return $this->activeTranslation->title;
    }

    public static function getTranslateModelClass()
    {
        return WebsiteTranslation::class;
    }

    public static function getTranslateForeignKeyName()
    {
        return 'website_id';
    }

    /**
     * @return array|Website|null
     */
    public static function getRootWebsite()
    {
        return Website::find()
            ->notDeleted()
            ->with([
                'currentTranslation',
                'defaultTranslation',
            ])
            ->innerJoin(
                ContentTree::tableName() . ' ct',
                'ct.record_id = ' . Website::tableName() . ".id and ct.table_name = '" . ContentTree::TABLE_NAME_WEBSITE . "' and depth = 0"
            )
            ->one();
    }
}
