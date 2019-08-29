<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use intermundia\yiicms\models\query\BaseTranslationQuery;
use Yii;
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
 * @property string $admin_email
 * @property string $bcc_email
 * @property string $cc_email

 *
 * @property Website $website
 */
class WebsiteTranslation extends BaseTranslateModel
{


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
            [['name'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 512],
            [['short_description'], 'string'],
            [['og_site_name'], 'string'],
            [['og_image_deleted', 'image_deleted', 'logo_image_deleted','additional_logo_image_deleted', 'claim_image_deleted'], 'safe'],
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
            ['image', 'file','maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['logo_image', 'file', 'maxFiles' => 20,'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['additional_logo_image', 'file', 'maxFiles' => 20,'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            ['claim_image', 'file', 'maxFiles' => 20,'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
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
            [['admin_email', 'cc_email', 'bcc_email'], 'checkValidEmail'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [

            'id' => Yii::t('intermundiacms', 'ID'),
            'logo_image_name' => Yii::t('intermundiacms', 'Logo Image'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'additional_logo_image_name' => Yii::t('intermundiacms', 'Additional Logo Image'),
            'copyright' => Yii::t('intermundiacms', 'Copyright'),
            'google_tag_manager_code' => Yii::t('intermundiacms', 'Google Tag Manager Code'),
            'html_code_before_close_body' => Yii::t('intermundiacms', 'Html Code Before Close Body'),
            'website_id' => Yii::t('intermundiacms', 'Website ID'),
            'language' => Yii::t('intermundiacms', 'Locale'),
            'address_of_company' => Yii::t('intermundiacms', 'Address Of Company'),
            'cookie_disclaimer_message' => Yii::t('intermundiacms', 'Cookie Disclaimer Message'),
            'title' => Yii::t('intermundiacms', 'Website Title'),
            'short_description' => Yii::t('intermundiacms', 'Short Description'),
            'footer_name' => Yii::t('intermundiacms', 'Footer Name'),
            'footer_headline' => Yii::t('intermundiacms', 'Footer Headline'),
            'footer_plain_text' => Yii::t('intermundiacms', 'Footer Plain Text'),
            'footer_copyright' => Yii::t('intermundiacms', 'Footer Copyright'),
            'footer_logo' => Yii::t('intermundiacms', 'Footer Logo'),
            'og_site_name' => Yii::t('intermundiacms', 'Og Site Name'),
            'image' => Yii::t('intermundiacms', 'Image'),
            'admin_email' => Yii::t('intermundiacms', 'Admin Email'),
            'bcc_email' => Yii::t('intermundiacms', 'BCC Email'),
            'cc_email' => Yii::t('intermundiacms', 'CC Email')
        ];
    }

    /**
     * @param string $attribute the attribute currently being validated
     */
    public function checkValidEmail($attribute)
    {
        $emails = explode(',', $this->{$attribute});
        foreach ($emails as $email) {
            if (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
                $this->addError($attribute, Yii::t('intermundiacms', 'Email: ' . trim($email) . ' is incorrect!!'));
            }
        }
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
}
