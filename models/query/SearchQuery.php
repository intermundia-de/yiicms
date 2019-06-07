<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Search]].
 *
 * @see \intermundia\yiicms\models\Search
 */
class SearchQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Search[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Search|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $contentTreeId
     * @return $this
     */
    public function byContentTreeId($contentTreeId)
    {
        return $this->andWhere(['content_tree_id' => $contentTreeId]);
    }

    /**
     * @param $language
     * @return $this
     */
    public function byLanguage($language)
    {
        return $this->andWhere(['language' => $language]);
    }
}
