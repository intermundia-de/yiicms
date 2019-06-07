<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\VideoSection;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\VideoSection]].
 *
 * @see \intermundia\yiicms\models\VideoSection
 */
class VideoSectionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\VideoSection[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\VideoSection|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $id
     * @return VideoSectionQuery
     */
    public function byId($id)
    {
        return $this->andWhere([VideoSection::tableName().'.id' => $id]);
    }
}
