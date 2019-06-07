<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\WebsiteTranslation]].
 *
 * @see \intermundia\yiicms\models\WebsiteTranslation
 */
class BaseQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\WebsiteTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\WebsiteTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $id
     * @return BaseQuery
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * @author Guga Grigolia <grigolia.guga@gmail.com>
     * @return BaseQuery
     */
    public function notDeleted()
    {
        return $this->andWhere(['deleted_at' => null, 'deleted_by' => null]);
    }
}
