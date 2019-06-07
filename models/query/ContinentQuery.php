<?php

namespace intermundia\yiicms\models\query;
use intermundia\yiicms\models\Continent;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Continent]].
 *
 * @see \intermundia\yiicms\models\Continent
 */
class ContinentQuery extends BaseQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Continent[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Continent|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function notDeleted()
    {
        return $this->andWhere([Continent::tableName() . '.deleted_at' => null]);
    }

    public function byId($id)
    {
        return $this->andWhere([Continent::tableName() . '.id' => $id]);
    }
}
