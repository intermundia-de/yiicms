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
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\models
 *
 * @property BaseTranslateModel   $activeTranslation
 * @property BaseTranslateModel   $currentTranslation
 * @property BaseTranslateModel   $defaultTranslation
 * @property ContentTree          $contentTree
 * @property BaseTranslateModel   $translation
 * @property BaseTranslateModel[] $translations
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
     * @return \yii\db\ActiveQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
    public function getActiveTranslationQuery()
    {
        return $this->currentTranslation ? $this->getCurrentTranslation() : $this->defaultTranslation ? $this->getDefaultTranslation() : $this->getTranslation();
    }

    /**
     * @return \intermundia\yiicms\models\BaseTranslateModel
     */
    public function getActiveTranslation()
    {
        return $this->currentTranslation ?: $this->defaultTranslation ?: $this->translations[0];
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
            'contentType' => $this->getContentType(),
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
        $translatedLanguages = $this->getTranslatedLanguages();
        uksort($translatedLanguages, function($a, $b) {
            if($a == Yii::$app->language || $a == Yii::$app->websiteMasterLanguage) {
                return -1;
            }
            else if($b == Yii::$app->language || $b == Yii::$app->websiteMasterLanguage) {
                return 1;
            }
            else return 0;
        });;
        foreach ($translatedLanguages as $code => $language) {
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
     * @param $fileManagerFilename
     * @param $index
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
     * @param $fileManagerFilename
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
     * Check if model has given $attributeName as file(s)
     *
     * @param $attributeName
     * @return bool
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function hasFile($attributeName)
    {
        return !!ArrayHelper::getValue($this->activeTranslation, $attributeName, []);
    }

    /**
     * Treat the attribute as an image and render <img> tag from the first element of the attribute array
     *
     * @param       $attributeName
     * @param bool  $resizeWidth
     * @param array $options
     * @param int   $imageIndex
     * @return string
     * @throws \yii\base\InvalidConfigException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function renderImage($attributeName, $resizeWidth = false, $options = [], $imageIndex = 0)
    {
        $images = ArrayHelper::getValue($this->activeTranslation, $attributeName, []);
        if ($images) {
            $url = $images[$imageIndex]->geturl();
            if ($resizeWidth) {
                $url = Yii::$app->glide->createSignedUrl([
                    'glide/index',
                    'path' => $images[$imageIndex]->path,
                    'w' => $resizeWidth
                ], true);
            }

            return Html::img($url, $options);
        }

        return '';
    }

    /**
     * Render images in im tags
     *
     * @param $attributeName
     * @param $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function renderImages($attributeName, $options)
    {
        $images = ArrayHelper::getValue($this->activeTranslation, $attributeName, []);

        return implode(' ', array_map(function ($image) use ($options) {
            return Html::img($image->geturl(), $options);
        }, $images));

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

    public function getContentType()
    {
        // @TODO Optimize this not to access contentTree
        return $this->contentTree->content_type;
    }
}
