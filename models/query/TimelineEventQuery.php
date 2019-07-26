<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 8/5/14
 * Time: 10:46 AM
 */

namespace intermundia\yiicms\models\query;

use yii\db\ActiveQuery;

class TimelineEventQuery extends ActiveQuery
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return self
     */
    public function today()
    {
        $this->andWhere(['>=', 'created_at', strtotime('today midnight')]);
        return $this;
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param null $db
     * @return array|null|\yii\db\ActiveRecord
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @param string $websiteKey
     * @return array|\yii\db\ActiveRecord[]
     */
    public function forWebsite($websiteKey)
    {
        return $this->andWhere(['website_key' => $websiteKey]);
    }
}
