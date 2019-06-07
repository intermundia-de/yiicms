<?php

namespace intermundia\yiicms\models\query;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\WebsiteTranslation]].
 *
 */
class BaseTranslationQuery extends \yii\db\ActiveQuery
{
    /**
     * @param $contentId
     * @param $language
     * @param $foreignKeyName
     * @return $this
     */
    public function findByObjectIdAndLanguage($contentId, $language, $foreignKeyName)
    {
        return $this->andWhere([$foreignKeyName => $contentId])
            ->andWhere(['language' => $language]);
    }

    /**
     * Find translation records by language
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $lang
     * @return BaseTranslationQuery
     */
    public function byLanguage($lang)
    {
        return $this->andWhere(['language' => $lang]);
    }
}
