<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\Menu]].
 *
 * @see \intermundia\yiicms\models\Menu
 */
class MenuQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Menu[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\Menu|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
