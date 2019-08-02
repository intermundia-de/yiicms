<?php
/**
 * User: zura
 * Date: 8/27/18
 * Time: 3:53 PM
 */

namespace intermundia\yiicms\web;

use common\models\ContentTree;
use http\Url;
use intermundia\yiicms\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Class BaseController
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms
 */
class Controller extends \yii\web\Controller
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \intermundia\yiicms\web\View|\yii\web\View
     */
    public function getView()
    {
        return parent::getView();
    }

    /**
     * {@inheritdoc}
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function render($view, $params = [])
    {
        $content = parent::render($view, $params);
        return Html::replaceContentTreeIdsInContent($content);
    }
}
