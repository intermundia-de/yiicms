<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%section_translation}}".
 *
 * @property int $id
 * @property int $section_id
 * @property string $language
 * @property string $template
 * @property string $title
 * @property string $description
 *
 * @property Section $section
 * @property Language $language0
 */
class SectionTranslation extends BaseTranslateModel
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%section_translation}}';
    }


    public function behaviors()
    {
        return array_merge([

        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['section_id'], 'integer'],
            [['template', 'description'], 'string'],
            [['language'], 'string', 'max' => 55],
            [['title'], 'string', 'max' => 2000],
            [['section_id'], 'exist', 'skipOnError' => true, 'targetClass' => Section::class, 'targetAttribute' => ['section_id' => 'id']],
            [['language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language' => 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'section_id' => Yii::t('common', 'Section ID'),
            'language' => Yii::t('common', 'Language'),
            'template' => Yii::t('common', 'Template'),
            'title' => Yii::t('common', 'Title'),
            'description' => Yii::t('common', 'Description'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSection()
    {
        return $this->hasOne(Section::class, ['id' => 'section_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage0()
    {
        return $this->hasOne(Language::class, ['code' => 'language']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\SectionTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\SectionTranslationQuery(get_called_class());
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getShortDescription()
    {
        return null;
    }

    public function getModelClass()
    {
        return Section::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'section_id';
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'language' => $this->language,
            'title' => $this->title,
            'template' => $this->template,
            'description' => $this->description
        ];
    }

    public function getEncodedTemplate()
    {
        return Html::encode($this->template);
    }
}
