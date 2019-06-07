<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\Language;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Language]].
 *
 * @see \intermundia\yiicms\models\Language
 */
class LanguageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Language[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Language|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byCode($code)
    {
        return $this->andWhere([Language::tableName() . '.code' => $code]);
    }
}
