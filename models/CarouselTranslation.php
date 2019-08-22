<?php

namespace intermundia\yiicms\models;

use Yii;

/**
 * This is the model class for table "{{%carousel_translation}}".
 *
 * @property int $id
 * @property int $carousel_id
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 *
 * @property Carousel $carousel
 */
class CarouselTranslation extends BaseTranslateModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%carousel_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['language', 'required'],
            [['carousel_id', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['language', 'name', ], 'string'],
            [['carousel_id'], 'exist', 'skipOnError' => true, 'targetClass' => Carousel::class, 'targetAttribute' => ['carousel_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'carousel_id' => Yii::t('intermundiacms', 'Carousel ID'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
            'created_by' => Yii::t('intermundiacms', 'Created By'),
            'updated_by' => Yii::t('intermundiacms', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCarousel()
    {
        return $this->hasOne(Carousel::class, ['id' => 'carousel_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\CarouselTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\CarouselTranslationQuery(get_called_class());
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
        return Carousel::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'carousel_id';
    }

    public function getData()
    {
        return $this->toArray();
    }
}
