<?php
/**
 * User: zura
 * Date: 9/21/18
 * Time: 2:14 PM
 */

namespace intermundia\yiicms\web;

use Yii;
use yii\base\InvalidArgumentException;
use yii\base\View;

/**
 * Class BackendController
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class BackendController extends Controller
{
    /**
     * Finds the applicable layout file.
     *
     * @param View $view the view object to render the layout file.
     * @return string|bool the layout file path, or false if layout is not needed.
     *                   Please refer to [[render()]] on how to specify this parameter.
     * @throws InvalidArgumentException if an invalid path alias is used to specify the layout.
     */
    public function findLayoutFile($view)
    {
        $module = $this->module;
        if (is_string($this->layout)) {
            $layout = $this->layout;
        } elseif ($this->layout === null) {
            while ($module !== null && $module->layout === null) {
                $module = $module->module;
            }
            if ($module !== null && is_string($module->layout)) {
                $layout = $module->layout;
            }
        }

        if (!isset($layout)) {
            return false;
        }

        $pathToLayout = null;
        if (strncmp($layout, '@', 1) === 0) {
            $file = Yii::getAlias($layout);
        } elseif (strncmp($layout, '/', 1) === 0) {
            $file = Yii::$app->getLayoutPath() . DIRECTORY_SEPARATOR . substr($layout, 1);
        } else {
            if (pathinfo($layout, PATHINFO_EXTENSION) === '') {
                $layout = $layout . '.' . $view->defaultExtension;
                if ($view->defaultExtension !== 'php' && !is_file($layout)) {
                    $layout = $layout . '.php';
                }
            }

            if (!file_exists($module->getLayoutPath() . DIRECTORY_SEPARATOR . $layout)) {
                $module = Yii::$app->getModule('core');
            }
            $file = $module->getLayoutPath() . DIRECTORY_SEPARATOR . $layout;
        }

        if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
            return $file;
        }
        $path = $file . '.' . $view->defaultExtension;
        if ($view->defaultExtension !== 'php' && !is_file($path)) {
            $path = $file . '.php';
        }

        return $path;
    }
}
