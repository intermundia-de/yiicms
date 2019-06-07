<?php
/**
 * User: zura
 * Date: 9/6/18
 * Time: 6:14 PM
 */

namespace intermundia\yiicms\widgets;


use intermundia\yiicms\models\FileManagerItem;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class FileInput
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\widgets
 */
class FileInput extends \kartik\file\FileInput
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var FileManagerItem
     */
    private $fileManagerItems = [];

    public function init()
    {
        $attribute = $this->attribute;
        if (ArrayHelper::getValue($this->options, 'multiple')) {
            $attribute = str_replace('[]', '', $this->attribute);
        }
        $this->fileManagerItems = $this->model->{$attribute};

        $this->pluginOptions = \yii\helpers\ArrayHelper::merge([
            'showUpload' => false,
            'overwriteInitial' => false
        ], $this->pluginOptions);

        parent::init();
    }

    public function registerAssetBundle()
    {
        $pluginOptions = $this->pluginOptions;
        $fileManagerItems = $this->fileManagerItems ?: [];

        $pluginOptions['initialPreview'] = [];
        $pluginOptions['initialCaption'] = [];
        $pluginOptions['initialPreviewConfig'] = [];
        foreach ($fileManagerItems as $fileManagerItem) {
            if ($fileManagerItem && $fileManagerItem instanceof FileManagerItem) {
                if ($fileManagerItem->isImage()) {
                    $pluginOptions['initialPreview'][] = Html::img($fileManagerItem->getUrl(),
                        ['style' => 'max-width: 100%']);
                } else if ($fileManagerItem->isVideo()) {
                    $pluginOptions['initialPreview'][] = \intermundia\yiicms\helpers\Html::video($fileManagerItem, null,
                        ['style' => 'max-width: 100%']);
                } else {
                    $pluginOptions['initialPreview'][] = Html::tag('iframe', '', [
                        'src' => $fileManagerItem->getUrl(),
                        'style' => 'max-width: 100%'
                    ]);
                }
                $pluginOptions['initialCaption'][] = $fileManagerItem->name;
                $pluginOptions['initialPreviewConfig'][] = [
                    'caption' => $fileManagerItem->name,
                    'size' => $fileManagerItem->size,
                    'key' => $fileManagerItem->id,
                    'url' => Url::to(['base/delete-file-item'])
                ];
            }
        }

        $this->pluginOptions = $pluginOptions;
        $this->registerWidgetJs('setTimeout(function () {
           var $input = $("#' . $this->options['id'] . '");
           var $wrapper = $input.closest(".file-input");
           $input.on("filebeforedelete", function (event, id, index) {
               var $deleteInput = $("#' . $this->options['id'] . '_deleted");
               if ($deleteInput.val() === ""){
                   $deleteInput.val(id);
               } else {
                   $deleteInput.val($deleteInput.val()+","+id);
               }
           });
       }, 1000);
');
        parent::registerAssetBundle();
    }

    protected function getInput($type, $list = false)
    {
        $deleteName = preg_replace('/^(\w+)+\[(\w+)](\[\])?/', '$1[$2_deleted]', $this->name);
        return parent::getInput($type,
                $list) . '<input id="' . $this->options['id'] . '_deleted" type="hidden" name="' . $deleteName . '">';
    }
}