<?php
/**
 * User: zura
 * Date: 9/20/18
 * Time: 7:30 PM
 */
namespace intermundia\yiicms\i18n;

use intermundia\yiicms\helpers\Html;
use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\FileManagerItem;
use yii\helpers\ArrayHelper;

/**
 * Class I18nFormatter
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package ${NAMESPACE}
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param FileManagerItem $fileManagerItem
     * @param array $options
     * @return string
     */
    public function asThumbnail($fileManagerItem, $options = [])
    {
        return Html::thumbnail($fileManagerItem, $options);
    }
}