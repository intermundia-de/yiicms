<?php


namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\Page;
use yii\db\ActiveQuery;

class PageQuery extends BaseQuery
{
    /**
     * @return $this
     */
    public function published()
    {
        $this->andWhere(['status' => 1]);
        return $this;
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $id
     * @return PageQuery
     */
    public function byId($id)
    {
        return $this->andWhere([Page::tableName().'.id' => $id]);
    }
}
