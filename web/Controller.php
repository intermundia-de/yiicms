<?php
/**
 * User: zura
 * Date: 8/27/18
 * Time: 3:53 PM
 */

namespace intermundia\yiicms\web;

use common\models\ContentTree;
use http\Url;
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
        preg_match_all('/(?<=\{{contentTreeId:)(.*?)(?=\}})/', $content, $matches);

        if (!isset($matches[1])) {
            return $content;
        }

        $aliasPaths = ArrayHelper::map(
            ContentTree::find()->byId($matches[1])->with(['currentTranslation', 'defaultTranslation'])->all(),
            'id',
            function ($model) {
                /** @var $model ContentTree */
                return [
                    'replace' => '/{{contentTreeId:' . $model->id . '}}/',
                    'alias_path' => \yii\helpers\Url::to([
                        'content-tree/index',
                        'nodes' => $model->activeTranslation->alias_path
                    ])
                ];
            });

        return preg_replace(
            ArrayHelper::getColumn($aliasPaths, 'replace'),
            ArrayHelper::getColumn($aliasPaths, 'alias_path'),
            $content
        );
    }
}
