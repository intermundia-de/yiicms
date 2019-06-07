<?php

namespace intermundia\yiicms\models;

use Yii;

/**
 * This is the model class for table "{{%video_section}}".
 *
 * @property int $id
 * @property int $deleted_at
 * @property int $deleted_by
 *
 * @property VideoSectionTranslation[] $translations
 * @property VideoSectionTranslation $activeTranslation
 */
class VideoSection extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_section}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'deleted_by'], 'integer'],
            [['created_at','updated_at', 'created_by','updated_by'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'deleted_at' => Yii::t('common', 'Deleted At'),
            'deleted_by' => Yii::t('common', 'Deleted By'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\VideoSectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\VideoSectionQuery(get_called_class());
    }

    public static function getTranslateModelClass()
    {
        return VideoSectionTranslation::class;
    }

    public static function getTranslateForeignKeyName()
    {
        return 'video_section_id';
    }
}
