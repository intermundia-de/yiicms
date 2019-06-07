<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\WidgetText;
/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\WidgetText]].
 *
 * @see \intermundia\yiicms\models\WidgetText
 */
class WidgetTextQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\WidgetText[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\WidgetText|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere([WidgetText::tableName().'.id' => $id]);
    }

    /**
     * @param $key
     * @return $this
     */
    public function byKey($key)
    {
        return $this->andWhere([WidgetText::tableName().'.key' => $key]);
    }
}
