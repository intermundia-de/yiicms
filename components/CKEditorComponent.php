<?php
/**
 * User: zura
 * Date: 6/23/18
 * Time: 6:32 PM
 */

namespace intermundia\yiicms\components;

use intermundia\yiicms\web\View;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\Application;


/**
 * Class CKEditorComponent
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package common\components
 */
class CKEditorComponent extends Component
{
    /**
     * Array of Ck Editor Styles. Each item must have `name`: string, `element`: "tag of html element" and `attributes`: associative array
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var array
     */
    public $customStyles = [];

    public function init()
    {
        parent::init();

        \Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
    }

    public function beforeRequest()
    {
        if (\Yii::$app->user->canEditContent()) {
            \Yii::$app->view->registerJs("
                if (typeof CKEDITOR !== 'undefined'){
                    CKEDITOR.stylesSet.add( 'default', " . Json::encode($this->customStyles) . " );
                }
            ");
        }
    }
}