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
use yii\helpers\Url;

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
     * @param FileManagerItem $fileManagerItem
     * @param $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
     * @param BaseModel|FileManagerItem $modelOrFileManagerItem
     * @param string $attribute The attribute is necessary only when $modelOrFileManagerItem is instance of BaseModel
     * @param array $options
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
     * @param ContentTree[] $contentTreeItems
     * @return array
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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

    /**
     * Replace contentTreeid array data in given content
     *
     * @param $content string
     * @return array
     */
    public static function replaceContentTreeIdsInContent($content)
    {

        preg_match_all('/(?<=\{{contentTreeId:)(.*?)(?=\}})/', $content, $matches);

        if (!isset($matches[1])) {
            return $content;
        }

        $aliasPaths = ArrayHelper::map(
            ContentTree::find()->byId($matches[1])->with('activeTranslation')->all(),
            'id',
            function ($model) {
                /** @var $model ContentTree */
                $urlData = [
                    'replace' => '/{{contentTreeId:' . $model->id . '}}/',
                    'alias_path' => Url::to(['/content-tree/index', 'nodes' => $model->activeTranslation->alias_path])
                ];
                if ($model->table_name != ContentTree::TABLE_NAME_PAGE) {
                    $pageAlias = $model->getPageUrl();
                    $alias_path = $model->activeTranslation->alias;
                    $urlData = [
                        'replace' => '/{{contentTreeId:' . $model->id . '}}/',
                        'alias_path' => $pageAlias . "#{$alias_path}_id_" . $model->id
                    ];
                }
                return $urlData;
            });

        return preg_replace(
            ArrayHelper::getColumn($aliasPaths, 'replace'),
            ArrayHelper::getColumn($aliasPaths, 'alias_path'),
            $content
        );
    }
}
