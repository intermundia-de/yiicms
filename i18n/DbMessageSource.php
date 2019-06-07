<?php
/**
 * User: zura
 * Date: 3/13/19
 * Time: 5:03 PM
 */

namespace intermundia\yiicms\i18n;


use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class DbMessageSource
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\i18n
 */
class DbMessageSource extends \yii\i18n\DbMessageSource
{
    protected function loadMessagesFromDb($category, $language)
    {
        $mainQuery = (new Query())->select([
            'message' => 't1.message',
            'translation' => "IF(t2.translation = '' OR t2.translation IS NULL, t3.translation, t2.translation)"
        ])
            ->from(['t1' => $this->sourceMessageTable])
            ->leftJoin( ['t2' => $this->messageTable], 't2.id = t1.id AND t2.language = :lang', ['lang' => $language])
            ->leftJoin( ['t3' => $this->messageTable], 't3.id = t1.id AND t3.language = :lang2', [
                'lang2' => \Yii::$app->websiteMasterLanguage
            ])
            ->where([
                't1.id' => new Expression('[[t2.id]]'),
                't1.category' => $category,
            ]);

//        $mainQuery->union($this->createFallbackQuery($category, $language, $fallbackSourceLanguage), true);

        $messages = $mainQuery->createCommand($this->db)->queryAll();

        $messages = ArrayHelper::map($messages, 'message', 'translation');

        return $messages;
    }
}