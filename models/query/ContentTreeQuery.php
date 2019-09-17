<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use creocoder\nestedsets\NestedSetsQueryBehavior;

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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return ContentTreeQuery
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return ContentTreeQuery
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $alias
     * @return ContentTreeQuery
     */
    public function byAlias($alias)
    {
        return $this->leftJoinOnTranslation()->andWhere([ContentTreeTranslation::tableName() . '.alias' => $alias]);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $aliasPath
     * @return ContentTreeQuery
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return ContentTreeQuery
     */
    public function notDeleted($alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();
        return $this->andWhere([$alias . '.deleted_at' => null]);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return ContentTreeQuery
     */
    public function notHidden()
    {
        return $this->andWhere([ContentTree::tableName() . '.hide' => 0]);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return ContentTreeQuery
     */
    public function hidden()
    {
        return $this->andWhere([ContentTree::tableName() . '.hide' => 1]);
    }
    /**
     * @return ContentTreeQuery
     */
    public function notHiddeInSiblings(){
        return $this->andWhere([ContentTree::tableName() . '.show_as_sibling' => 0]);
    }

    /**
     * @return ContentTreeQuery
     */
    public function hiddeInSiblings(){
        return $this->andWhere([ContentTree::tableName() . '.show_as_sibling' => 1]);
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $contentTreeId
     * @param null $language
     * @return ContentTreeQuery
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
     * @author Guga Grigolia <grigolia.guga@gmail.com>
     * @param $key
     * @return ContentTreeQuery
     */
    public function byKey($key)
    {
        return $this->andWhere([ContentTree::tableName() . '.key' => $key]);
    }

    /**
     * Find for particular root
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param int $id
     * @param null $alias
     * @return ContentTreeQuery
     */
    public function forRoot(int $id, $alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();
        return $this->andWhere([$alias . '.website' => $id]);
    }

    /**
     * @author Mirian Jintchvelashvili
     * @return ContentTreeQuery
     */
    public function inSitemap()
    {
        return $this->andWhere([ContentTree::tableName() . '.in_sitemap' => true]);
    }
}
