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
        if (strpos($langCode, "-") === false){
            throw new InvalidConfigException('$langCode argument must be language in the format: "en-US"');
        }
        return explode("-", $langCode)[0];
    }
}