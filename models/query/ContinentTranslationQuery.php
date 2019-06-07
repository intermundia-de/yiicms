<?php

namespace intermundia\yiicms\models\query;
use intermundia\yiicms\models\ContinentTranslation;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\ContinentTranslation]].
 *
 * @see \intermundia\yiicms\models\ContinentTranslation
 */
class ContinentTranslationQuery extends BaseTranslationQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContinentTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContinentTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function notDeleted()
    {
        return $this->andWhere([ContinentTranslation::tableName() . '.deleted_at' => null]);
    }

    public function byLanguage($lang)
    {
        return $this->andWhere([ContinentTranslation::tableName() . '.language' => $lang]);
    }

}
