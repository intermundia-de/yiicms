<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\ContentTreeMenu]].
 *
 * @see \intermundia\yiicms\models\ContentTreeMenu
 */
class ContentTreeMenuQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTreeMenu[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\ContentTreeMenu|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param $contentTreeId
     * @return $this
     */
    public function byContentTreeId($contentTreeId)
    {
        return $this->andWhere(['content_tree_id' => $contentTreeId]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * @param $id
     * @return $this
     */
    public function byMenuId($menuId)
    {
        return $this->andWhere(['menu_id' => $menuId]);
    }
}
