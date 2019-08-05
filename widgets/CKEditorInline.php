<?php

namespace intermundia\yiicms\widgets;

use dosamigos\ckeditor\CKEditorTrait;
use dosamigos\ckeditor\CKEditorWidgetAsset;
use intermundia\yiicms\bundle\CKEditorAsset;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\jui\InputWidget;


/**
 * Class CKEditorInline
 *
 * @author Mirian Jintchvelashvili
 * @package common\widgets
 */
class CKEditorInline extends CKEditor
{
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */

    /**
     * @var string the toolbar preset.
     * Defaults to 'inline'
     */
    public $preset = 'inline';

    public $options = [];
    /**
     * @var bool disables creating the inline editor automatically for elements with contenteditable attribute
     * set to the true. Defaults to true.
     */
    public $disableAutoInline = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if($this->preset == 'inline') {
            $this->clientOptions = self::inlinePreset();
        }

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        $this->options['contenteditable'] = 'true';
    }

    public static function inlinePreset() {
        return [
            'height' => 200,
            'toolbarGroups' =>
            [
                ['name' => 'document', 'groups' => ['mode', 'document', 'doctools']],
                ['name' => 'undo'],
                ['name' => 'basicstyles', 'groups' => ['basicstyles']],
            ],
            'extraPlugins' => 'sourcedialog',
            'removeButtons' => 'Strike',
            'resize_enabled' => false
        ];
    }

    /**
     * Registers CKEditor plugin
     * @codeCoverageIgnore
     */
    protected function registerPlugin()
    {
        $js = [];

        $view = $this->getView();

        CKEditorWidgetAsset::register($view);

        $id = $this->options['id'];

        $options = $this->clientOptions !== false && !empty($this->clientOptions)
            ? Json::encode($this->clientOptions)
            : '{}';

        if ($this->disableAutoInline) {
            $js[] = "CKEDITOR.disableAutoInline = true;";
        }
        $js[] = "CKEDITOR.inline('$id', $options);";

        if (isset($this->clientOptions['filebrowserUploadUrl'])) {
            $js[] = "dosamigos.ckEditorWidget.registerCsrfImageUploadHandler();";
        }

        $view->registerJs(implode("\n", $js));
    }
}
