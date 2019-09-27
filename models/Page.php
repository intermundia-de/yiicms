<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\SearchBehavior;
use intermundia\yiicms\models\query\PageQuery;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "page".
 *
 * @property integer $id
 * @property string $view
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PageTranslation[] $translations
 * @property PageTranslation $activeTranslation
 */
class Page extends BaseModel
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%page}}';
    }

    /**
     * @return PageQuery
     */
    public static function find()
    {
        return new PageQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge([
            TimestampBehavior::class
        ],parent::behaviors());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['view'], 'string', 'max' => 255],
            [['deleted_at', 'deleted_by'], 'integer'],
            [['created_at','updated_at', 'created_by','updated_by'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'view' => Yii::t('intermundiacms', 'Page View'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
        ];
    }

    public function getTitle()
    {
        return $this->activeTranslation ? $this->activeTranslation->title : '';
    }

    public static function getTranslateModelClass()
    {
        return PageTranslation::class;
    }

    public static function getTranslateForeignKeyName()
    {
        return 'page_id';
    }
}
