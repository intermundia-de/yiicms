<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use intermundia\yiicms\models\query\BaseTranslationQuery;
use Yii;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "page_translation".
 *
 * @property int $id
 * @property int $page_id
 * @property string $language
 * @property string $title
 * @property string $short_description
 * @property string $body
 * @property string $meta_title
 * @property string $meta_keywords
 * @property string $meta_description
 *
 * @property Page $page
 */
class PageTranslation extends BaseTranslateModel
{

    /**
     * @author Guga Grigolia <grigolia.guga@gmail.com>
     * @var UploadedFile|FileManagerItem
     */
    public $image;

    /**
     * @var
     */
    public $image_deleted;


    public function behaviors()
    {
        return array_merge([
            FileManagerItemBehavior::class => [
                'class' => FileManagerItemBehavior::class,
                'columnNames' => ['image' => 'image'],
            ]
        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%page_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language', 'title'], 'required'],
            [['page_id'], 'integer'],
            [['body'], 'string'],
            [['image_deleted'], 'safe'],
            ['image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg, webp'],
            [['language'], 'string', 'max' => 15],
            [['short_description'], 'string'],
            [['title', 'meta_title', 'meta_keywords', 'meta_description'], 'string', 'max' => 512],
            [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['page_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'page_id' => Yii::t('intermundiacms', 'Page ID'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'title' => Yii::t('intermundiacms', 'Title'),
            'short_description' => Yii::t('intermundiacms', 'Short Description'),
            'body' => Yii::t('intermundiacms', 'Body'),
            'meta_title' => Yii::t('intermundiacms', 'Meta Title'),
            'meta_keywords' => Yii::t('intermundiacms', 'Meta Keywords'),
            'meta_description' => Yii::t('intermundiacms', 'Meta Description'),
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
    public function getPage()
    {
        return $this->hasOne(Page::class, ['id' => 'page_id']);
    }

    public function getModelClass()
    {
        return Page::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'page_id';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return StringHelper::truncate($this->short_description, 1000);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'language' => $this->language,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'body' => $this->body,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
        ];
    }
}
