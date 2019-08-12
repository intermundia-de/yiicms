<?php

namespace intermundia\yiicms\modules\translation\models;

use intermundia\yiicms\modules\translation\traits\ModuleTrait;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%i18n_source_message}}".
 *
 * @property integer       $id
 * @property string        $category
 * @property string        $message
 *
 * @property Translation[] $translations
 */
class Source extends ActiveRecord
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%i18n_source_message}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
            [['category'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'       => Yii::t('intermundia\yiicms', 'ID'),
            'category' => Yii::t('intermundia\yiicms', 'Category'),
            'message'  => Yii::t('intermundia\yiicms', 'Message'),
        ];
    }

    /** @inheritdoc */
    public function __get($name)
    {
        if (in_array($name, array_keys($this->getLanguages()))) {
            return $this->getTranslation($name);
        }

        return parent::__get($name);
    }

    /**
     * @param $language
     *
     * @return Translation|null
     */
    public function getTranslation($language)
    {
        foreach ($this->translations as $translation) {
            if (str_replace('-', '_', $translation->language) == $language) return $translation;
        }

        return null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(Translation::class, ['id' => 'id']);
    }
}
