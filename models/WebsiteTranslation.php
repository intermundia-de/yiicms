<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use intermundia\yiicms\models\query\BaseTranslationQuery;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "website_translation".
 *
 * @property int $id
 * @property int $website_id
 * @property int $min
 * @property int $max
 * @property string $language
 * @property string $short_description
 * @property string $general_legal_note
 * @property string $obligatory_information
 * @property string $address_of_company
 * @property string $cookie_disclaimer_message
 * @property string $footer_promotion
 * @property string $title
 * @property string $omit_title_on_homepage
 * @property string $page_header
 * @property string $meta_tags
 * @property string $og_site_name
 *
 * @property string $contact_type
 * @property string $telephone
 * @property string $social_links
 * @property string $company_country
 * @property string $company_city
 * @property string $company_postal_code
 * @property decimal $location_latitude
 * @property decimal $location_longitude
 * @property string $company_business_hours
 *
 *
 * @property Website $website
 */
class WebsiteTranslation extends BaseTranslateModel
{
    /**
     * @var []
     */
    public $businessHoursShedule;

    public $socialLinkArray;
    /**
     * @var UploadedFile|FileManagerItem
     */
    public $og_image;
    public $og_image_deleted;
    /**
     * @var UploadedFile|FileManagerItem
     */
    public $claim_image;
    public $claim_image_deleted;
    /**
     * @var UploadedFile|FileManagerItem
     */
    public $additional_logo_image;
    public $additional_logo_image_deleted;
    /**
     * @var UploadedFile|FileManagerItem
     */
    public $logo_image;
    public $logo_image_deleted;

    /**
     * @var UploadedFile|FileManagerItem
     */
    public $image;
    public $image_deleted;


    public $aliasMutable = true;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%website_translation}}';
    }

    public function behaviors()
    {
        return array_merge([
            FileManagerItemBehavior::class => [
                'class' => FileManagerItemBehavior::class,
                'columnNames' => [
                    'og_image' => 'og_image',
                    'logo_image' => 'logo_image',
                    'additional_logo_image' => 'additional_logo_image',
                    'claim_image' => 'claim_image',
                    'image' => 'image'
                ],
            ],
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
                    ActiveRecord::EVENT_BEFORE_VALIDATE => 'social_links',

                ],
                'skipUpdateOnClean' => false,
                'value' => function () {
                    return Json::encode($this->social_links);
                }
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'social_links',
                ],
                'value' => function () {
                    return Json::decode($this->social_links);
                }
            ]
        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['website_id',], 'integer'],
            [['language'], 'string', 'max' => 15],
            [['name', 'contact_type', 'telephone', 'company_country', 'company_city', 'company_postal_code'], 'string', 'max' => 255],
            ['location_latitude', 'number', 'numberPattern' => '/^\-?(90|[0-8]?[0-9])\.[0-9]{0,6}$/',
                'message' => 'Value must be in WGS84 format'],
            ['location_longitude', 'number', 'numberPattern' => '/^\-?(180|1[0-7][0-9]|[0-9]{0,2})\.[0-9]{0,6}$/',
                'message' => 'Value must be in WGS84 format'],
            [['short_description'], 'string'],
            [['og_site_name'], 'string'],
            [['og_image_deleted', 'image_deleted', 'logo_image_deleted', 'additional_logo_image_deleted', 'claim_image_deleted'], 'safe'],
            [
                [
                    'footer_name',
                    'footer_headline',
                    'footer_plain_text',
                    'footer_copyright',
                    'footer_logo'
                ],
                'string'
            ],
            ['og_image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['logo_image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['additional_logo_image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['claim_image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            [
                [
                    'logo_image_name',
                    'additional_logo_image_name',
                    'copyright',
                    'google_tag_manager_code',
                    'html_code_before_close_body'
                ],
                'string',
                'max' => 255
            ],
            [
                [
                    'address_of_company',
                    'cookie_disclaimer_message',
                    'title',
                    'social_links'
                ],
                'string',
                'max' => 512
            ],
            [
                ['website_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Website::className(),
                'targetAttribute' => ['website_id' => 'id']
            ],
            ['businessHoursShedule', 'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [

            'id' => Yii::t('common', 'ID'),
            'logo_image_name' => Yii::t('common', 'Logo Image'),
            'name' => Yii::t('common', 'Name'),
            'additional_logo_image_name' => Yii::t('common', 'Additional Logo Image'),
            'copyright' => Yii::t('common', 'Copyright'),
            'google_tag_manager_code' => Yii::t('common', 'Google Tag Manager Code'),
            'html_code_before_close_body' => Yii::t('common', 'Html Code Before Close Body'),
            'website_id' => Yii::t('common', 'Website ID'),
            'language' => Yii::t('common', 'Locale'),
            'address_of_company' => Yii::t('common', 'Street Address Of Company'),
            'cookie_disclaimer_message' => Yii::t('common', 'Cookie Disclaimer Message'),
            'title' => Yii::t('common', 'Website Title'),
            'short_description' => Yii::t('common', 'Short Description'),
            'footer_name' => Yii::t('common', 'Footer Name'),
            'footer_headline' => Yii::t('common', 'Footer Headline'),
            'footer_plain_text' => Yii::t('common', 'Footer Plain Text'),
            'footer_copyright' => Yii::t('common', 'Footer Copyright'),
            'footer_logo' => Yii::t('common', 'Footer Logo'),
            'og_site_name' => Yii::t('common', 'Og Site Name'),
            'image' => Yii::t('common', 'Image'),
            'contact_type' => Yii::t('common', 'Contact Type'),
            'telephone' => Yii::t('common', 'Telephone'),
            'social_links' => Yii::t('common', 'Social Links'),
            'company_country' => Yii::t('common', 'Country Of Company'),
            'company_city' => Yii::t('common', 'City Of Company'),
            'company_postal_code' => Yii::t('common', 'Postal Code Of Company'),
            'location_latitude' => Yii::t('common', 'Geolocation Latitude'),
            'location_longitude' => Yii::t('common', 'Geolocation Longitude'),
        ];
    }

    /**
     * @return BaseTranslationQuery
     */
    public static function find()
    {
        return new BaseTranslationQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWebsite()
    {
        return $this->hasOne(Website::class, ['id' => 'website_id']);
    }

    public function getModelClass()
    {
        return Website::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'website_id';
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getShortDescription()
    {
        return StringHelper::truncate($this->short_description, 1000);
    }


    /**
     * @return array
     */
    public function getData()
    {
        return $this->toArray();
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
