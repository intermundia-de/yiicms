<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\CountryTranslation;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\CountryTranslation]].
 *
 * @see \intermundia\yiicms\models\CountryTranslation
 */
class CountryTranslationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CountryTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\CountryTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function notDeleted()
    {
        return $this->andWhere([CountryTranslation::tableName() . '.deleted_at' => null]);
    }

    public function byLanguage($lang)
    {
        return $this->andWhere([CountryTranslation::tableName() . '.language' => $lang]);
    }

}
