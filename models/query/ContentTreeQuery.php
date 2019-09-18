<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use creocoder\nestedsets\NestedSetsQueryBehavior;
use Yii;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\ContentTree]].
 *
 * @see \intermundia\yiicms\models\ContentTree
 */
class ContentTreeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    public function behaviors()
    {
        return [
            NestedSetsQueryBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTree[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTree|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return ContentTreeQuery
     */
    public function byId($id)
    {
        return $this->andWhere([ContentTree::tableName() . '.id' => $id]);
    }

    /**
     * @param $id
     * @return ContentTreeQuery
     */
    public function byRecordId($id)
    {
        return $this->andWhere([ContentTree::tableName() . '.record_id' => $id]);
    }

    /**
     *
     */
    public function linkedIdIsNull()
    {
        return $this->andWhere([ContentTree::tableName() . '.link_id' => null]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byIdOrLinkId($id)
    {
        return $this->andWhere([
            'or',
            [ContentTree::tableName() . '.link_id' => $id],
            [ContentTree::tableName() . '.id' => $id]
        ]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byLinkId($id, $alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return $this->andWhere([$alias . '.link_id' => $id]);
    }

    /**
     * @param $tableName
     * @param $id
     * @return ContentTreeQuery
     */
    public function byRecordIdTableName($id, $tableName)
    {
        return $this->andWhere([ContentTree::tableName() . '.record_id' => $id])
            ->andWhere([ContentTree::tableName() . '.table_name' => $tableName]);
    }

    /**
     * @param $name
     * @return ContentTreeQuery
     */
    public function byTableName($name)
    {
        return $this->andWhere([ContentTree::tableName() . '.table_name' => $name]);
    }

    /**
     * @param string $contentType
     * @return ContentTreeQuery
     */
    public function byContentType($contentType)
    {
        return $this->andWhere([ContentTree::tableName() . '.content_type' => $contentType]);
    }

    /**
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function leftJoinOnTranslation()
    {
        $tr = ContentTreeTranslation::tableName();

        return $this->leftJoin($tr . 't',
            ' t' . '.content_tree_id = ' . ContentTree::tableName() . ".id AND t.language = :language", [
                'language' => \Yii::$app->language
            ])->leftJoin($tr . 'tt',
            ' tt' . '.content_tree_id = ' . ContentTree::tableName() . ".id AND tt.language = :masterLanguage", [
                'masterLanguage' => \Yii::$app->websiteMasterLanguage
            ]);
    }

    /**
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function innerJoinOnActiveTranslation()
    {
        $tr = ContentTreeTranslation::tableName();

        return $this->innerJoin($tr,
            $tr . '.content_tree_id = ' . ContentTree::tableName() . ".id AND $tr.language = :language", [
                'language' => \Yii::$app->language
            ]);
    }

    /**
     * @param $alias
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function byAlias($alias)
    {
        return $this->leftJoinOnTranslation()->andWhere([ContentTreeTranslation::tableName() . '.alias' => $alias]);
    }

    /**
     * @param $aliasPath
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function byAliasPath($aliasPath)
    {
        return $this->leftJoinOnTranslation()
            ->andWhere("if(t.id IS NOT null, t.alias_path = :t_alias_path, tt.alias_path = :t_alias_path)")
            ->addParams([
                ':t_alias_path' => $aliasPath
            ]);
    }

    /**
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function notDeleted($alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return $this->andWhere([$alias . '.deleted_at' => null]);
    }

    /**
     * @param string $alias
     * @return \intermundia\yiicms\models\query\ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function deleted($alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return $this->andWhere(['not', ["$alias.deleted_at" => null]]);
    }

    /**
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function notHidden()
    {
        return $this->andWhere([ContentTree::tableName() . '.hide' => 0]);
    }

    /**
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function hidden()
    {
        return $this->andWhere([ContentTree::tableName() . '.hide' => 1]);
    }

    /**
     * @return ContentTreeQuery
     */
    public function notHiddeInSiblings()
    {
        return $this->andWhere([ContentTree::tableName() . '.show_as_sibling' => 0]);
    }

    /**
     * @return ContentTreeQuery
     */
    public function hiddeInSiblings()
    {
        return $this->andWhere([ContentTree::tableName() . '.show_as_sibling' => 1]);
    }

    /**
     *
     *
     * @param      $contentTreeId
     * @param null $language
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function byIdAndLanguage($contentTreeId, $language = null)
    {
        $language = $language ?: \Yii::$app->language;

        return $this->leftJoinOnTranslation()
            ->andWhere([
                ContentTree::tableName() . '.id' => $contentTreeId,
                ContentTreeTranslation::tableName() . '.language' => $language
            ]);
    }

    /**
     *
     *
     * @param $key
     * @return ContentTreeQuery
     * @author Guga Grigolia <grigolia.guga@gmail.com>
     */
    public function byKey($key)
    {
        return $this->andWhere([ContentTree::tableName() . '.key' => $key]);
    }

    /**
     * Find for particular root
     *
     * @param int  $id
     * @param null $alias
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function forRoot(int $id, $alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return $this->andWhere([$alias . '.website' => $id]);
    }

    /**
     * Adds ->with(['currentTranslation', 'defaultTranslation'])
     * If you pass $linkTranslations argument as true it will add also
     * ->with(['link.currentTranslation', 'link.defaultTranslation']);
     *
     * @param bool $linkTranslations
     * @return self
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function withTranslations($linkTranslations = false)
    {
        $this->with(['currentTranslation', 'defaultTranslation']);
        if ($linkTranslations) {
            $this->with(['link.currentTranslation', 'link.defaultTranslation']);
        }

        return $this;
    }

    /**
     * Search ContentTree by view
     *
     * @param string $view
     * @param string $alias
     * @return \intermundia\yiicms\models\query\ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function byView($view, $alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return $this->andWhere(["$alias.view" => $view]);
    }

    /**
     * Find only ContentTree which has translation on current language or default language
     *
     * @return self
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function hasTranslation()
    {
        $ctt = ContentTreeTranslation::tableName();
        $this->leftJoin($ctt . ' ctt', ContentTree::tableName() . ".id = ctt.content_tree_id AND ctt.language = :language", [
            'language' => Yii::$app->language,
        ]);
        if (Yii::$app->language !== Yii::$app->websiteMasterLanguage) {
            $this->leftJoin($ctt . ' ctt2', ContentTree::tableName() . ".id = ctt2.content_tree_id AND ctt2.language = :masterLanguage", [
                'masterLanguage' => Yii::$app->websiteMasterLanguage
            ])
                ->andWhere('ctt.id IS NOT NULL OR ctt2.id IS NOT NULL');
        } else {
            $this->andWhere('ctt.id IS NOT NULL');
        }

        return $this;
    }

    /**
     * Returns ContentTree as tree
     *
     * @return \intermundia\yiicms\models\query\ContentTreeQuery
     */
    public function tree()
    {
        $query = ContentTree::findBySql("
            SELECT `content_tree`.`id`,
                   `content_tree`.`record_id`,
                   `content_tree`.`link_id`,
                   `content_tree`.`table_name`,
                   `content_tree`.`lft`,
                   `content_tree`.`rgt`,
                   `content_tree`.`depth`,
                   `content_tree`.`hide`,
                   IFNULL(ct.alias, IFNULL(ctt.alias, (SELECT alias FROM content_tree_translation WHERE content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) LIMIT 1))) AS `alias`,
                   IFNULL(ct.name, IFNULL(ctt.name, (SELECT `name` FROM content_tree_translation WHERE content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) LIMIT 1))) AS `name`,
                   IFNULL(ct.short_description, IFNULL(ctt.short_description, (SELECT short_description FROM content_tree_translation WHERE content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) LIMIT 1))) AS `short_description`,
                   IFNULL(ct.language, IFNULL(ctt.language, (SELECT language FROM content_tree_translation WHERE content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) LIMIT 1))) AS `language`
            FROM `content_tree`
                     LEFT JOIN `content_tree_translation` `ct` ON
                ct.content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) AND ct.language = :language
                     LEFT JOIN `content_tree_translation` `ctt` ON
                ctt.content_tree_id = IFNULL(`content_tree`.link_id, `content_tree`.id) AND ctt.language = :masterLanguage
            WHERE (`content_tree`.`website` = :website)
              AND (`content_tree`.`deleted_at` IS NULL)
            ORDER BY `content_tree`.`lft`
        ", [
            'website' => Yii::$app->websiteContentTree->id,
            'language' => \Yii::$app->language,
            'masterLanguage' => \Yii::$app->websiteMasterLanguage
        ]);

        return $query;
    }
}
