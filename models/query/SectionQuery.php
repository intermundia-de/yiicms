<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\query\CountryTranslationQuery]].
 *
 * @see \intermundia\yiicms\models\Section
 */
class SectionQuery extends BaseQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Section[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Section|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
