<?php
/**
 * User: zura
 * Date: 2/28/19
 * Time: 4:34 PM
 */

namespace intermundia\yiicms\helpers;


use yii\base\InvalidConfigException;

/**
 * Class LanguageHelper
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\helpers
 */
class LanguageHelper
{
    public static function convertLongCodeIntoShort($langCode)
    {
        if (!preg_match('/^[a-z]{2}$/', $langCode) && !preg_match('/^[a-z]{2,3}\-[A-Z]{2}$/', $langCode) ){
            throw new InvalidConfigException('$langCode argument must be language in the format: "en" or "en-US"');
        }
        return explode("-", $langCode)[0];
    }
}