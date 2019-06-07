<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\ContentTreeTranslation;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\ContentTreeTranslation]].
 *
 * @see \intermundia\yiicms\models\ContentTreeTranslation
 */
class ContentTreeTranslationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTreeTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTreeTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $alias
     * @return ContentTreeTranslationQuery
     */
    public function byAlias($alias)
    {
        return $this->andWhere([ContentTreeTranslation::tableName() . '.alias' => $alias]);
    }

    /**
     * @param $aliasPath
     * @return ContentTreeTranslationQuery
     */
    public function byAliasPath($aliasPath)
    {
        return $this->andWhere([ContentTreeTranslation::tableName() . '.alias_path' => $aliasPath]);
    }

    /**
     * @param $id
     * @return ContentTreeTranslationQuery
     */
    public function except($id)
    {
        return $this->andWhere(['NOT', [ContentTreeTranslation::tableName() . '.id' => $id]]);
    }

    /**
     * @param $aliasPath
     * @return ContentTreeTranslationQuery
     */
    public function startWith($aliasPath)
    {
        return $this->andWhere(['LIKE', ContentTreeTranslation::tableName() . '.alias_path', $aliasPath . '%', false]);
    }

    /**
     * @param $language
     * @return ContentTreeTranslationQuery
     */
    public function byLanguage($language)
    {
        return $this->andWhere([ContentTreeTranslation::tableName() . '.language' => $language]);
    }

    /**
     * @param $language
     * @param $treeId
     * @return ContentTreeTranslationQuery
     */
    public function byLanguageAndTreeId($treeId, $language = null)
    {
        return $this->andWhere([ContentTreeTranslation::tableName() . '.language' => $language])
            ->andWhere([ContentTreeTranslation::tableName() . '.content_tree_id' => $treeId]);
    }

    /**
     * @param $language
     * @param $treeId
     * @return ContentTreeTranslationQuery
     */
    public function byTreeId($treeId)
    {
        return $this->andWhere([ContentTreeTranslation::tableName() . '.content_tree_id' => $treeId]);
    }
}
