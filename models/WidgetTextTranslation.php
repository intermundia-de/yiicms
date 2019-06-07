<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use intermundia\yiicms\behaviors\StorageUrlBehavior;

/**
 * This is the model class for table "widget_text_translation".
 *
 * @property int $id
 * @property int $widget_text_id
 * @property string $language
 * @property string $title
 * @property string $body
 * @property string $short_description
 *
 * @property WidgetText $widgetText
 */
class WidgetTextTranslation extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%widget_text_translation}}';
    }

    public function behaviors()
    {
        return array_merge([
            [
                'class' => StorageUrlBehavior::class,
                'columnNames' => 'body'
            ]
        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['language', 'title'], 'required'],
            [['widget_text_id'], 'integer'],
            [['body', 'short_description'], 'string'],
            [['language'], 'string', 'max' => 15],
            [['title'], 'string', 'max' => 512],
            [['widget_text_id'], 'exist', 'skipOnError' => true, 'targetClass' => WidgetText::className(), 'targetAttribute' => ['widget_text_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'widget_text_id' => Yii::t('app', 'Widget Text ID'),
            'language' => Yii::t('app', 'Language'),
            'title' => Yii::t('app', 'Title'),
            'body' => Yii::t('app', 'Body'),
            'short_description' => Yii::t('app', 'Short Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWidgetText()
    {
        return $this->hasOne(WidgetText::class, ['id' => 'widget_text_id']);
    }

    public function getModelClass()
    {
        return WidgetText::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'widget_text_id';
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getShortDescription()
    {
        return StringHelper::truncate($this->short_description, 1000);
    }

    public function setWidgetTextId($id)
    {
        $this->widget_text_id = $id;
        return $this;
    }

}
