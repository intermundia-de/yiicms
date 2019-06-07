<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\query\CountryTranslationQueryTranslation]].
 *
 * @see \intermundia\yiicms\models\SectionTranslation
 */
class SectionTranslationQuery extends BaseTranslationQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\SectionTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\SectionTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
