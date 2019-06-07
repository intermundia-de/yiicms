<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%carousel_item_translation}}".
 *
 * @property int $id
 * @property int $carousel_item_id
 * @property string $caption
 * @property string $name
 * @property int $language
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property CarouselItem $carouselItem
 */
class CarouselItemTranslation extends BaseTranslateModel
{

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var UploadedFile|FileManagerItem
     */
    public $image;

    public $image_deleted;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%carousel_item_translation}}';
    }

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
    public function rules()
    {
        return [
            [['language', 'name'], 'required'],
            [['carousel_item_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['language', 'caption', 'name'], 'string'],
            [['image_deleted', ], 'safe'],
            [
                'image',
                'file',
                'maxFiles' => 20,
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, svg'
            ],
            [
                ['language'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Language::class,
                'targetAttribute' => ['language' => 'code']
            ],
            [
                ['carousel_item_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => CarouselItem::class,
                'targetAttribute' => ['carousel_item_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'carousel_item_id' => Yii::t('common', 'Carousel Item ID'),
            'caption' => Yii::t('common', 'Caption'),
            'name' => Yii::t('common', 'Name'),
            'language' => Yii::t('common', 'Language'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCarouselItem()
    {
        return $this->hasOne(CarouselItem::class, ['id' => 'carousel_item_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\CarouselItemTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\CarouselItemTranslationQuery(get_called_class());
    }

    public function getTitle()
    {
        return $this->name;
    }

    public function getShortDescription()
    {
        return null;
    }

    public function getModelClass()
    {
        return CarouselItem::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'carousel_item_id';
    }

    public function getData()
    {
        return $this->toArray();
    }
}
