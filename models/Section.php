<?php

namespace intermundia\yiicms\models;

use Yii;

/**
 * This is the model class for table "{{%section}}".
 *
 * @property int $id
 * @property int $deleted_at
 * @property int $deleted_by
 *
 * @property SectionTranslation[] $translations
 * @property SectionTranslation $activeTranslation
 */
class Section extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%section}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['deleted_at', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer']

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\SectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\SectionQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedBy()
    {
        return $this->hasOne(User::class, ['id' => 'deleted_by']);
    }

    public static function getTranslateModelClass()
    {
        return SectionTranslation::class;
    }

    public static function getTranslateForeignKeyName()
    {
        return 'section_id';
    }

    public function replaceTemplateVars($content, $contentTreeItem = null)
    {
        $contentTreeItem = $contentTreeItem ?: $this->contentTree;
        $template = str_replace('{{content}}', $content, $this->activeTranslation->template);
        $template = str_replace('{{alias}}', 'alias_' . $this->activeTranslation->alias, $template);
        $template = str_replace('{{cssClasses}}', $contentTreeItem->getCssClass(), $template);
        $template = str_replace('{{editableAttributes}}', $contentTreeItem->getEditableAttributesForSection('section'), $template);

        return $template;
    }
}
