<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\VideoSectionTranslation]].
 *
 * @see \intermundia\yiicms\models\VideoSectionTranslation
 */
class VideoSectionTranslationQuery extends BaseTranslationQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\VideoSectionTranslation[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\VideoSectionTranslation|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
