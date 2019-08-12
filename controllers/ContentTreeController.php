<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 7:01 PM
 */

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\web\BackendController;
use intermundia\yiicms\models\ContentTree;
use Yii;
use yii\web\NotFoundHttpException;


/**
 * Class ContentTreeController
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\controllers
 */
class ContentTreeController extends BackendController
{
    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $nodes
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($nodes = '')
    {
        if (!$nodes) {
            $nodes = \Yii::$app->defaultAlias;
        }

        $alias_path = preg_replace('@^' . Yii::$app->websiteContentTree->getAlias() . '\/@', '', $nodes);
        $contentTreeItem = $this->findContentTreeByFullPath($alias_path);


        $model = $contentTreeItem->getModel();
        if ($contentTreeItem->record_id !== -1 && !$model) {
            throw new NotFoundHttpException("Content is not editable");
        }

        return $this->render('index', [
            'contentTreeItem' => $contentTreeItem
        ]);
    }

    public function actionUpdate($id)
    {
        $model = ContentTreeTranslation::find()->byTreeId($id)->one();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect($model->contentTree->getFullUrl());
        } else {
            return $this->render('_update_tree_item', [
                'model' => $model,
            ]);
        }
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $alias
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
     */
    protected function findContentTree($alias)
    {
        if (!($contentTree = ContentTree::find()->byAlias($alias)->notDeleted()->one())) {
            throw new NotFoundHttpException("Incorrect alias given");
        }
        return $contentTree;
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $aliasPath
     * @return \intermundia\yiicms\models\ContentTree|array|null
     * @throws NotFoundHttpException
     */
    protected function findContentTreeByFullPath($aliasPath)
    {
        if (!($contentTree = ContentTree::find()->byAliasPath($aliasPath)->notDeleted()->one())) {
            throw new NotFoundHttpException("Incorrect alias Path given");
        }
        return $contentTree;
    }
}