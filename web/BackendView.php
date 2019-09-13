<?php
/**
 * User: zura
 * Date: 8/27/18
 * Time: 3:53 PM
 */

namespace intermundia\yiicms\web;


use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\Page;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class BackendView
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class BackendView extends \yii\web\View
{
    public function render($view, $params = [], $context = null)
    {
        if (strpos($view, '@') !== 0) {
            $moduleId = Yii::$app->controller->module->id;
            $filePath = FileHelper::normalizePath(Yii::$app->controller->id . "/$view.php");
            if ($moduleId === 'backend' || $moduleId === 'core') {
                if (file_exists(Yii::getAlias("@backend/views/$filePath"))) {
                    $view = "@backend/views/" . $filePath;
                } else {
                    $view = "@cmsCore/views/" . $filePath;
                }
            }

        }
        return parent::render($view, $params, $context);
    }

}