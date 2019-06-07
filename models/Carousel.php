<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%carousel}}".
 *
 * @property int $id
 * @property string $key
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 */
class Carousel extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%carousel}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            TimestampBehavior::class,
            BlameableBehavior::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'key' => Yii::t('common', 'Key'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\CarouselQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\CarouselQuery(get_called_class());
    }

    public static function getTranslateModelClass()
    {
        return CarouselTranslation::class;
    }

    public static function getTranslateForeignKeyName()
    {
        return 'carousel_id';
    }
}
