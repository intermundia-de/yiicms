<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\Country;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Country]].
 *
 * @see \intermundia\yiicms\models\Country
 */
class CountryQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Country[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Country|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function notDeleted()
    {
        return $this->andWhere([Country::tableName() . '.deleted_at' => null]);
    }

    public function byId($id)
    {
        return $this->andWhere([Country::tableName() . '.id' => $id]);
    }

    public function byIsoCode1($code)
    {
        return $this->andWhere([Country::tableName() . '.iso_code_1' => $code]);
    }

    public function active()
    {
        return $this->andWhere([Country::tableName() . '.status' => 1]);
    }
}
