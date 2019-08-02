<?php
/**
 * Created by PhpStorm.
 * User: miriani
 * Date: 29/11/19
 * Time: 5:44 PM
 */

namespace intermundia\yiicms\models\query;

use yii\db\ActiveQuery;

class TimelineEventQuery extends ActiveQuery
{
    /**
     * @return self
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function today()
    {
        $this->andWhere(['>=', 'created_at', strtotime('today midnight')]);
        return $this;
    }

    /**
     * @param null $db
     * @return array|\yii\db\ActiveRecord[]
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @param null $db
     * @return array|null|\yii\db\ActiveRecord
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
        return $this->orWhere(['website_key' => $websiteKey])
               ->orWhere(['website_key' => null]);
    }
}
