<?php
/**
 * User: zura
 * Date: 9/20/18
 * Time: 7:23 PM
 */

namespace intermundia\yiicms\helpers;

use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\FileManagerItem;
use yii\helpers\ArrayHelper;

/**
 * Class Html
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package ${NAMESPACE}
 */
class Html extends \yii\helpers\Html
{
    /**
     * Generate thumbnail image for BaseModel $attribute
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param FileManagerItem $fileManagerItem
     * @param $options
     * @return string
     */
    public static function thumbnail(FileManagerItem $fileManagerItem, $options)
    {
        $options = ArrayHelper::merge([
            'style' => 'width: 120px'
        ], $options);
        return Html::img($fileManagerItem->getUrl(), $options);
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param BaseModel|FileManagerItem $modelOrFileManagerItem
     * @param string $attribute The attribute is necessary only when $modelOrFileManagerItem is instance of BaseModel
     * @param array $options
     * @return string
     */
    public static function video($modelOrFileManagerItem, $attribute = null, $options = [])
    {
        /** @var FileManagerItem[] $videos */

        if ($modelOrFileManagerItem instanceof BaseModel) {
            $videos = $modelOrFileManagerItem->activeTranslation->{$attribute};
        } else {
            $videos = [$modelOrFileManagerItem];
        }
        $sources = [];
        foreach ($videos as $video) {
            $sources[] = \yii\bootstrap\Html::tag('source', '', [
                'src' => $video->getUrl(),
                'type' => $video->type
            ]);
        }
        return \yii\bootstrap\Html::tag('video', implode(PHP_EOL, $sources), $options);
    }

    /**
     * Convert ContentTree array data into [[Nav]] compatible `items` array
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param ContentTree[] $contentTreeItems
     * @return array
     */
    public static function convertToNavData($contentTreeItems)
    {
        $navData = [];
        foreach ($contentTreeItems as $contentTreeItem) {
            $model = $contentTreeItem->getModel();
            $navData[] = [
                'label' => $model->getTitle(),
                'url' => $contentTreeItem->getUrl()
            ];
        }
        return $navData;
    }
}