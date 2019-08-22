<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\WebsiteQuery;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\UploadedFile;

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
 * @property string $company_name
 * @property string $company_country
 * @property string $company_city
 * @property string $company_street_address
 * @property string $company_postal_code
 * @property decimal $location_latitude
 * @property decimal $location_longitude
 * @property string $company_contact_type
 * @property string $company_telephone
 * @property string $company_social_links
 * @property string $company_business_hours
 *
 * @property WebsiteTranslation[] $translations
 * @property WebsiteTranslation $activeTranslation
 */
class Website extends BaseModel
{
    /**
     * @var []
     */
    public $businessHoursShedule;
    public $socialLinkArray;

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
            TimestampBehavior::class,
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'company_business_hours',
                    ActiveRecord::EVENT_BEFORE_UPDATE => 'company_business_hours',

                ],
                'skipUpdateOnClean' => false,
                'value' => function () {
                    foreach ($this->businessHoursShedule as $day => $dayShedule) {
                        if (!$dayShedule['startTime'] || !$dayShedule['endTime']) {
                            unset($this->businessHoursShedule[$day]);
                        }
                    }
                    return Json::encode($this->businessHoursShedule);
                }
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'businessHoursShedule',
                ],
                'value' => function () {
                    return Json::decode($this->company_business_hours);
                }
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_VALIDATE => 'company_social_links',

                ],
                'skipUpdateOnClean' => false,
                'value' => function () {
                    return Json::encode($this->company_social_links);
                }
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'company_social_links',
                ],
                'value' => function () {
                    return Json::decode($this->company_social_links);
                }
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['company_name', 'company_contact_type', 'company_telephone', 'company_country', 'company_city', 'company_street_address', 'company_postal_code'], 'string', 'max' => 255],
            ['location_latitude', 'number', 'numberPattern' => '/^\-?(90|[0-8]?[0-9])\.[0-9]{0,6}$/',
                'message' => 'Value must be in WGS84 format'],
            ['location_longitude', 'number', 'numberPattern' => '/^\-?(180|1[0-7][0-9]|[0-9]{0,2})\.[0-9]{0,6}$/',
                'message' => 'Value must be in WGS84 format'],
            ['company_social_links', 'string', 'max' => 2048],
            ['businessHoursShedule', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
            'company_name' => Yii::t('intermundiacms', 'Company Name'),
            'company_country' => Yii::t('intermundiacms', 'Country'),
            'company_city' => Yii::t('intermundiacms', 'City'),
            'company_street_address' => Yii::t('intermundiacms', 'Street Address'),
            'company_postal_code' => Yii::t('intermundiacms', 'Postal Code'),
            'company_contact_type' => Yii::t('intermundiacms', 'Contact Type'),
            'company_telephone' => Yii::t('intermundiacms', 'Telephone'),
            'company_social_links' => Yii::t('intermundiacms', 'Social Links'),
            'location_latitude' => Yii::t('intermundiacms', 'Geolocation Latitude'),
            'location_longitude' => Yii::t('intermundiacms', 'Geolocation Longitude'),
            'company_business_hours' => Yii::t('intermundiacms', 'Business Hours')
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

    /**
     * @return array
     */
    public function getWeekDays()
    {
        return [
            'Monday',
            'Tuesday',
            'Wednsday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ];
    }
}
