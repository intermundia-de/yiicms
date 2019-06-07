<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\Carousel;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Carousel]].
 *
 * @see \intermundia\yiicms\models\Carousel
 */
class CarouselQuery extends BaseQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Carousel[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Carousel|array|null
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
        return $this->andWhere([Carousel::tableName().'.id' => $id]);
    }
}
