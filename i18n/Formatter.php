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
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\i18n
 */
class Formatter extends \yii\i18n\Formatter
{
    /**
     * Renders FileManager Items as images
     *
     * @param FileManagerItem[] $fileManagerItems
     * @param array             $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function asThumbnail($fileManagerItems, $options = [])
    {

        return implode(' ', array_map(function ($fileManagerItem) use ($options) {
            $options = ArrayHelper::merge([
                'alt' => $fileManagerItem->name,
                'style' => 'width: 200px;'
            ], $options);

            return \yii\helpers\Html::img($fileManagerItem->getUrl(), $options);
        }, $fileManagerItems));
    }

    /**
     * Renders PDF links for given file manager items
     *
     * @param FileManagerItem[] $fileManagerItems
     * @param array             $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function asPdf($fileManagerItems, $options = ['target' => '_blank'])
    {
        return implode('<br/>', array_map(function ($fileManagerItem) use ($options) {
            return \yii\helpers\Html::a($fileManagerItem->name, $fileManagerItem->getUrl(), $options);
        }, $fileManagerItems));
    }
}