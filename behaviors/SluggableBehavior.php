<?php

namespace intermundia\yiicms\behaviors;

use intermundia\yiicms\components\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class SluggableBehavior
 * @package intermundia\yiicms\behaviors
 */
class SluggableBehavior extends \yii\behaviors\SluggableBehavior
{
    /**
     * Where key will be replaced with value in slug generation proccess. For example,
     *
     * 'value' must contains lation characters
     *
     * ```php
     * [
     *     'key' => 'value',
     *     'ä' => 'ae',
     * ]
     * ```
     *
     * Based on the above example, if slug is equal to "slug-example-ä" it's generates "slug-example-ae"
     *
     * @var array
     */
    public $replaceWords;

    /**
     * Updates alias and aliasPath in any case if it's true .
     *
     * Defaults to false, meaning it's depend on other constraints.
     *
     * @var bool
     */
    public $forceUpdate = false;


    /**
     * @var string
     */
    public $aliasPathAttribute = 'alias_path';

    /**
     * Only chechk alias_path is unique and not modify alias and alias_path if it's true.
     *
     * Defaults to false, meaning it's depend on other constraints.
     *
     * @var bool
     */
    public $onlyMakeUniqueInPath = false;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_VALIDATE => $this->slugAttribute,
                BaseActiveRecord::EVENT_AFTER_VALIDATE => $this->aliasPathAttribute,
            ];
        }

        if ($this->attribute === null && $this->value === null) {
            throw new InvalidConfigException('Either "attribute" or "value" property must be specified.');
        }
    }

    /**
     * Generates slug
     *
     * @param array $slugParts
     * @return string
     */
    protected function generateSlug($slugParts)
    {
        $slugParts = $this->modifySlugAttribute($slugParts);
        return parent::generateSlug($slugParts);
    }

    /**
     * Checks if new slug is needed
     *
     * @return bool
     */
    protected function isNewSlugNeeded()
    {
        return $this->forceUpdate ? $this->forceUpdate : parent::isNewSlugNeeded();
    }

    /**
     * @param $event
     * @return mixed|string
     */
    protected function getValue($event)
    {
        if ($event->name === BaseActiveRecord::EVENT_BEFORE_VALIDATE) {
            return $this->getAlias();
        }

        return $this->getAliasPath();
    }

    /**
     * @return mixed|string
     */
    protected function getAlias()
    {
        if (!$this->isNewSlugNeeded() || $this->onlyMakeUniqueInPath) {
            return $this->owner->{$this->slugAttribute};
        }

        if ($this->attribute !== null) {
            $slugParts = [];
            foreach ((array)$this->attribute as $attribute) {
                $part = \yii\helpers\ArrayHelper::getValue($this->owner, $attribute);
                if ($this->skipOnEmpty && $this->isEmpty($part)) {
                    return $this->owner->{$this->slugAttribute};
                }
                $slugParts[] = $part;
            }
            $slug = $this->generateSlug($slugParts);
        } else {
            $slug = parent::getValue($event);
        }

        return $this->ensureUnique ? $this->makeUnique($slug) : $slug;
    }

    /**
     * @return string
     */
    protected function getAliasPath()
    {
        if ($this->onlyMakeUniqueInPath) {
            return $this->ensureAliasPathUnique($this->owner->{$this->aliasPathAttribute});
        }
        /** @var  $contentTreeTranslation ContentTreeTranslation */
        $contentTreeTranslation = $this->owner;
        $language = $contentTreeTranslation->language;
        $masterLanguage = \Yii::$app->websiteMasterLanguage;
        $contentTree = $contentTreeTranslation->contentTree;
        /** @var ContentTree $parentContentTree */
        $parentContentTree = $contentTree->getParent();
        $aliasPath = $contentTreeTranslation->alias;
        if ($parentContentTree && $contentTree->depth > 1) {
            /** @var ContentTreeTranslation $parentContentTreeTranslation */
            $parentContentTreeTranslation = $parentContentTree->getTranslation()->andWhere(['language' => $language])->one();
            if ($parentContentTreeTranslation) {
                return $this->ensureAliasPathUnique($parentContentTreeTranslation->alias_path . '/' . $contentTreeTranslation->alias);
            }
            $aliasPath = '';
            $parentContentTrees = $contentTree
                ->parents()
                ->joinWith('translations')
                ->andWhere(['>', \intermundia\yiicms\models\ContentTree::tableName() . '.depth', 0])
                ->asArray()
                ->all();

            foreach ($parentContentTrees as $contentTree) {
                $translations = ArrayHelper::index($contentTree['translations'], 'language');
                $parentTranslationAlias = isset($translations[$language]) ? $translations[$language]['alias'] : $translations[$masterLanguage]['alias'];
                $aliasPath .= "$parentTranslationAlias/";
            }

            return $this->ensureAliasPathUnique($aliasPath . $contentTreeTranslation->alias);
        }

        return $this->ensureAliasPathUnique($contentTreeTranslation->alias);
    }

    /**
     * Ensure unique alias_path and language combination.
     * If it's not unique add "-$numeric" end of alias and alias path
     * $numeric = maximum(numeric value end of alias_path after last '-') + 1
     *
     * @param $aliasPath
     * @return string
     */
    protected function ensureAliasPathUnique($aliasPath)
    {
        $ct = ContentTreeTranslation::find()
            ->byAliasPath($aliasPath)
            ->byLanguage($this->owner->language)
            ->except($this->owner->id)
            ->innerJoinWith('contentTree')
            ->andWhere([\intermundia\yiicms\models\ContentTree::tableName() . '.deleted_at' => null])
            ->count();

        if ($ct == 0) {
            return $aliasPath;
        }

        $numericAliasPath = array_map(function ($contentTreeTranslation) {
            $explode = explode('-', $contentTreeTranslation['alias_path']);
            $lastElement = end($explode);
            return is_numeric($lastElement) ? intval($lastElement) : 0;
        }, ContentTreeTranslation::find()->select('alias_path')->startWith($aliasPath)->asArray()->all());
        $numeric = max($numericAliasPath) + 1;
        $this->owner->alias = $this->owner->alias . '-' . $numeric;

        return $aliasPath . '-' . $numeric;
    }

    /**
     * @param $slugParts
     * @return array|string
     */
    protected function modifySlugAttribute($slugParts)
    {
        $slugPartArray = [];
        if ($this->replaceWords) {
            foreach ($slugParts as $slugPart) {
                foreach ($this->replaceWords as $key => $replaceWord) {
                    $slugPart = str_replace($key, $replaceWord, $slugPart);
                }
                $slugPartArray [] = strip_tags($slugPart);
            }
            return $slugPartArray;
        }
        return '';
    }
}
