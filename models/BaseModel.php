<?php
/**
 * Created by PhpStorm.
 * User: guga
 * Date: 6/19/18
 * Time: 9:07 PM
 */

namespace intermundia\yiicms\models;


use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/**
 * Class BaseModel
 *
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\models
 *
 * @property BaseTranslateModel $activeTranslation
 * @property BaseTranslateModel $currentTranslation
 * @property BaseTranslateModel $defaultTranslation
 * @property ContentTree $contentTree
 * @property BaseTranslateModel $translation
 */
abstract class BaseModel extends ActiveRecord implements BaseModelInterface
{
    public $alias;
    public $alias_path;
    public $oldAlias;
    public $oldAliasPath;
    public $parentContentId;
    public $contentTreeId;
    public $language;
    public $treeName;
    public $short_description;


    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \yii\db\ActiveQuery
     */
    public function getContentTree()
    {
        return $this->hasOne(ContentTree::class,
            ['record_id' => 'id'])->andWhere(['table_name' => $this->getFormattedTableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslation()
    {
        return $this->hasOne(static::getTranslateModelClass(), [static::getTranslateForeignKeyName() => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(static::getTranslateModelClass(), [static::getTranslateForeignKeyName() => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActiveTranslation()
    {
        if ($this->currentTranslation) {
            return $this->getCurrentTranslation();
        }
        return $this->getDefaultTranslation();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultTranslation()
    {
        return $this->hasOne(static::getTranslateModelClass(), [static::getTranslateForeignKeyName() => 'id'])
            ->andWhere(['language' => Yii::$app->websiteMasterLanguage]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentTranslation()
    {
        return $this->hasOne(static::getTranslateModelClass(), [static::getTranslateForeignKeyName() => 'id'])
            ->andWhere(['language' => Yii::$app->language]);
    }

    
    public static function getFormattedTableName()
    {
        return preg_replace('/^(\{\{%)|(}}$)/', '', self::tablename());
    }

    public static function getTableNameUpperCase()
    {
        return implode(" ", array_map('ucfirst', explode('_', self::getFormattedTableName())));
    }
    
    public function getParent()
    {
        return ContentTree::find()->byRecordIdTableName($this->id,
            $this->getFormattedTableName())->one()->parents(1)->one();
    }

    public function getParentId()
    {
        return $this->getParent() ? $this->getParent()->id : 0;
    }

    /**
     * @return array
     */
    public function getUpdateUrl()
    {
        return [
            'base/update',
            'contentType' => $this->contentTree->content_type,
            'parentContentId' => $this->getParentId(),
            'contentId' => $this->id,
            'language' => $this->getActiveTranslationLanguageCode() ?: Yii::$app->language
        ];
    }

    /**
     * @return array
     */
    public function getUpdateUrlByLanguage($languageCode)
    {
        return [
            'base/update',
            'contentType' => $this->getFormattedTableName(),
            'parentContentId' => $this->getParentId(),
            'contentId' => $this->id,
            'language' => $languageCode
        ];
    }

    /**
     * @return array
     */
    public function getNewTranslationUrl()
    {
        return [
            'base/add-new-language',
            'tableName' => $this->getFormattedTableName(),
            'id' => $this->id,
            'from' => Yii::$app->websiteMasterLanguage,
            'to' => Yii::$app->language,
        ];
    }

    public function getDeleteUrl($treeId)
    {
        return [
            'base/delete',
            'tableName' => $this->getFormattedTableName(),
            'contentTreeId' => $treeId,
            'id' => $this->id,
        ];
    }

    public function getTranslatedLanguages()
    {
        return array_intersect_key(Yii::$app->websiteLanguages, ArrayHelper::map($this->translations, 'language', 'language'));
    }

    public function getNotTranslatedLanguages()
    {
        return array_diff_key(Yii::$app->websiteLanguages, ArrayHelper::map($this->translations, 'language', 'language'));
    }


    public function getUpdateTranslationItems()
    {
        $items = [];
        foreach ($this->getTranslatedLanguages() as $code => $language) {
            $items[] = ['label' => $language, 'url' => $this->getUpdateUrlByLanguage($code)];
        }
        return $items;
    }

    public function getContentTranslationDeleteUrl($treeId, $language)
    {
        return [
            'base/delete-translation',
            'tableName' => $this->getFormattedTableName(),
            'contentTreeId' => $treeId,
            'language' => $language
        ];
    }

    public function getTitle()
    {
        return $this->activeTranslation ? $this->activeTranslation->title : null;
    }

    public function getShortDescription()
    {
        return $this->activeTranslation ? $this->activeTranslation->getShortDescription() : null;
    }

    private function getActiveTranslationLanguageCode()
    {
        return $this->activeTranslation ? $this->activeTranslation->language : null;
    }

    /**
     * Return FileManagerItem path from `activeTranslation`'s $fileManagerFilename attribute
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $fileManagerFilename
     * @param $index
     * @return string
     */
    public function getUrlForFile($fileManagerFilename, $index = 0)
    {
        if ($this->activeTranslation) {
            return $this->activeTranslation->getUrlForFile($fileManagerFilename, $index);
        }
        return '';
    }

    /**
     * Return FileManagerItem path from `activeTranslation`'s $fileManagerFilename attribute
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $fileManagerFilename
     * @return string
     */
    public function getAttrForFile($fileManagerFilename, $attr)
    {
        return $this->activeTranslation->getAttrForFile($fileManagerFilename, $attr);
    }

    public function getRichTextField($fieldName, ContentTree $contentTreeItem = null, $classes = '')
    {
        if (Yii::$app->user->canEditContent() || $this->activeTranslation->{$fieldName}) {
            $contentTreeItem = $contentTreeItem ?: $this->contentTree;
            return '<div class="xmlblock ' . $classes . '" ' . $contentTreeItem->getEditableAttributes($fieldName, 'rich-text') . '>
                    ' . $this->activeTranslation->{$fieldName} . '
                </div>';
        }
        return '';
    }

    /**
     * Treat the attribute as an image and render <img> tag from the first element of the attribute array
     *
     * @param $attributeName
     * @param array $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function renderImage($attributeName, $options = [])
    {
        $images = ArrayHelper::getValue($this->activeTranslation, $attributeName, []);
        if ($images){
            return Html::img($images[0]->geturl(), $options);
        }
        return '';
    }

    /**
     * Return the content attribute of activeTranslation
     *
     * @param string $attribute
     * @return mixed
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function renderAttribute($attribute)
    {
        return $this->activeTranslation->$attribute;
    }

    /**
     * @param string $dependentAttributeName
     * Dependent attribute name, which value in form input be compared against $compareValue
     * @param string $compareValue
     * Value to be compared against $dependentAttributeName value
     * @param bool $trueOnMatch
     * determines whether JS validating function returns true or false
     * when $compareValue == $dependentAttributeName value in form input
     *
     * @return string
     * returns JS code for client side validation
     * @author Mirian Jintchvelashvili
     */
    protected function clientSideValidatorCondition($dependentAttributeName, $compareValue, $trueOnMatch) {
        return 'function(attribute,value){
                let required = $("#'.\yii\helpers\Html::getInputId($this, $dependentAttributeName).'").val()==="'.$compareValue.'";
              return '. ($trueOnMatch ? '' : '!') .'required;
            }';
    }
}
