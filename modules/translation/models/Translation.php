<?php

namespace intermundia\yiicms\modules\translation\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%i18n_message}}".
 *
 * @property integer $id
 * @property string $language
 * @property string $translation
 * @property string $sourceMessage
 * @property string $category
 *
 * @property Source $sourceMessageModel
 */
class Translation extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%i18n_message}}';
    }

    /** @inheritdoc */
    public function formName()
    {
        return str_replace('-', '_', $this->language);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            [['id'], 'exist', 'targetClass' => Source::class, 'targetAttribute' => 'id'],
            [['translation'], 'string'],
            [['language'], 'string', 'max' => 16],
            [['language'], 'unique', 'targetAttribute' => ['id', 'language']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'translation' => Yii::t('intermundiacms', 'Translation'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSourceMessageModel()
    {
        return $this->hasOne(Source::class, ['id' => 'id']);
    }
}