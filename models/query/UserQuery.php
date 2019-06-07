<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\User;
use yii\db\ActiveQuery;

/**
 * Class UserQuery
 * @package intermundia\yiicms\models\query
 * @author Eugene Terentev <eugene@terentev.net>
 */
class UserQuery extends ActiveQuery
{
    /**
     * @return $this
     */
    public function notDeleted()
    {
        $this->andWhere(['!=', 'status', User::STATUS_DELETED]);
        return $this;
    }

    /**
     * @return $this
     */
    public function active()
    {
        $this->andWhere(['status' => User::STATUS_ACTIVE]);
        return $this;
    }
}