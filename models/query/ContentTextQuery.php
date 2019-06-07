<?php

namespace intermundia\yiicms\models\query;
use intermundia\yiicms\models\ContentText;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\ContentText]].
 *
 * @see \intermundia\yiicms\models\ContentText
 */
class ContentTextQuery extends BaseQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentText[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentText|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return ContentTextQuery
     */
    public function byId($id)
    {
        return $this->andWhere([ContentText::tableName().'.id' => $id]);
    }
}
