<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\CarouselItem;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\CarouselItem]].
 *
 * @see \intermundia\yiicms\models\CarouselItem
 */
class CarouselItemQuery extends BaseQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CarouselItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CarouselItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return self
     */
    public function byId($id)
    {
        return $this->andWhere([CarouselItem::tableName().'.id' => $id]);
    }
}
