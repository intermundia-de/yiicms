<?php

namespace intermundia\yiicms\models\query;


/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\CarouselTranslation]].
 *
 * @see \intermundia\yiicms\models\CarouselTranslation
 */
class CarouselTranslationQuery extends BaseTranslationQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CarouselTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CarouselTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
