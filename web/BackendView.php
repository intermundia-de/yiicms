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
            $moduleId = $this->getModulePath();
            $filePath = FileHelper::normalizePath(Yii::$app->controller->id . "/$view.php");

            if ($moduleId === 'backend' || $moduleId === 'backend/core') {
                if (file_exists(Yii::getAlias("@backend/views/$filePath"))) {
                    $view = "@backend/views/" . $filePath;
                } else {
                    $view = "@cmsCore/views/" . $filePath;
                }
            } elseif (strpos($moduleId, 'backend/core/') === 0) {
                $modulePrefix = str_replace('backend/core/', '', $moduleId);
                if (file_exists(Yii::getAlias("@backend/views/$modulePrefix/$filePath"))) {
                    $view = "@backend/views/$modulePrefix/$filePath";
                }
            }

        }
        return parent::render($view, $params, $context);
    }


    /**
     * @param $module
     * @return bool
     */
    private function getModulePath()
    {
        $module = Yii::$app->controller->module;
        $corePath = [];
        while (isset($module->id)) {
            $corePath [] = $module->id;
            $module = $module->module;
        }
        $rev = array_reverse($corePath);

        return implode('/', $rev);
    }

}
