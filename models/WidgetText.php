<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\WidgetTextQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "widget_text".
 * @property int $id
 * @property string $key
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 *
 * @property WidgetTextTranslation $activeTranslation
 * @property WidgetTextTranslation $defaultTranslation
 * @property WidgetTextTranslation $currentTranslation
 * @property WidgetTextTranslation $translation
 * @property WidgetTextTranslation[] $translations
 */
class WidgetText extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DRAFT = 0;

    public $title;
    public $body;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%widget_text}}';
    }
    /**
     * @return WidgetTextQuery
     */
    public static function find()
    {
        return new WidgetTextQuery(get_called_class());
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
            [['key'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'key' => Yii::t('intermundiacms', 'Key'),
            'status' => Yii::t('intermundiacms', 'Status'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(WidgetTextTranslation::class, ['widget_text_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveTranslation()
    {
        if ($this->currentTranslation){
            return $this->getCurrentTranslation();
        }
        return $this->getDefaultTranslation();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultTranslation()
    {
        return $this->hasOne(WidgetTextTranslation::class, ['widget_text_id' => 'id'])->andWhere(['language' => Yii::$app->websiteMasterLanguage])->cache(10000);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentTranslation()
    {
        return $this->hasOne(WidgetTextTranslation::class, ['widget_text_id' => 'id'])->andWhere(['language' => Yii::$app->language])->cache(10000);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslation()
    {
        return $this->hasOne(WidgetTextTranslation::class, ['widget_text_id' => 'id']);
    }

    /**
     * @return array statuses list
     */
    public static function statuses()
    {
        return [
            self::STATUS_DRAFT => Yii::t('intermundiacms', 'Draft'),
            self::STATUS_ACTIVE => Yii::t('intermundiacms', 'Active'),
        ];
    }

    public function getTitle()
    {
        return $this->activeTranslation ? $this->activeTranslation->title : '';
    }
}
