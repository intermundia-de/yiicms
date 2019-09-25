<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 7:01 PM
 */

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\models\BaseModel;
use common\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use Yii;
use intermundia\yiicms\web\Controller;
use yii\filters\ContentNegotiator;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * Class ContentTreeController
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package frontend\controllers
 */
class FrontendContentTreeController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::class,
                'only' => ['edit-content', 'hide-section', 'get-tree', 'link-tree'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->selectPageContentTree();
//        $this->selectBaseModels();
        return parent::beforeAction($action);
    }

    /**
     *
     *
     * @return string
     * @throws NotFoundHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function actionIndex()
    {
        $contentTreeItem = Yii::$app->pageContentTree;

        $model = $contentTreeItem->getModel();

        if ($contentTreeItem->record_id !== -1 && !$model) {
            throw new NotFoundHttpException("Content is not editable");
        }

//        $this->getView()->contentTreeObject = $contentTreeItem;
        return $this->render('index', [
            'contentTreeItem' => $contentTreeItem,
            'model' => $model,
        ]);
    }

    public function actionEditContent()
    {
        $language = \Yii::$app->request->post('language');
        $contentId = \Yii::$app->request->post('content-id');
        $attribute = \Yii::$app->request->post('attribute');
        $type = \Yii::$app->request->post('type');
        $contentText = \Yii::$app->request->post('content');
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $content = $this->findContentTreeById($contentId);

        /** @var BaseModel $model */
        $model = $content->getModel();
        $translation = $model->getTranslation()->andWhere(['language' => $language])->one();

        if (!$model || !$translation) {
            throw new NotFoundHttpException();
        }
        if ($type != 'image') {
            $translation->$attribute = $type == 'rich-text' ? $contentText : strip_tags($contentText);
        }
        if ($translation->save()) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false
        ];
    }

    public function actionHideSection()
    {

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $contentTree = ContentTree::find()->byId(intval(Yii::$app->request->post('contentId')))->one();
        $contentTree->hide = intval(Yii::$app->request->post('state'));
        /** @var BaseModel $model */

        if ($contentTree->save()) {
            return [
                'success' => true
            ];
        }

        return [
            'success' => false
        ];
    }

    /**
     *
     * @throws \Exception
     */
    public function actionGetTree()
    {
        $tree = ContentTree::getItemsAsTreeForLink(null, ['page']);
        return json_encode($tree);
    }

    /**
     * @return array
     */
    public function actionLinkTree()
    {
        $res = ['success' => false];
        $parentId = intval(Yii::$app->request->post('parentId'));
        $currentLinkId = intval(Yii::$app->request->post('linkId'));
        $parentLinkId = intval(Yii::$app->request->post('parentLinkId'));

        $parentContentTree = ContentTree::find()->byId($parentId)->linkedIdIsNull()->one();
        $linkedParentTree = ContentTree::find()->byId($parentLinkId)->linkedIdIsNull()->one();

        if ($parentContentTree && $linkedParentTree) {
            $linkedTree = new ContentTree();
            $linkedTree->link_id = $linkedParentTree->id;
            $linkedTree->record_id = $linkedParentTree->record_id;
            $linkedTree->table_name = $linkedParentTree->table_name;
            if ($linkedTree->appendTo($parentContentTree)) {
                $currentLinkTree = ContentTree::find()->byId($currentLinkId)->one();
                $currentLinkTree->markDelete();
                $res = ['success' => true, 'url' => $linkedParentTree->getUrl()];
            }
        }

        return json_encode($res);
    }

    /**
     *
     *
     * @param $alias
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    protected function findContentTree($alias)
    {
        if (!($contentTree = ContentTree::find()->byAlias($alias)->one())) {
            throw new NotFoundHttpException("Incorrect alias given");
        }
        return $contentTree;
    }

    /**
     *
     *
     * @param $id
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    protected function findContentTreeById($id)
    {
        if (!($contentTree = ContentTree::find()->byId($id)->one())) {
            throw new NotFoundHttpException();
        }
        return $contentTree;
    }


    /**
     *
     *
     * @throws NotFoundHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    protected function selectPageContentTree()
    {
        Yii::$app->pageContentTree = $this->findContentTreeByFullPath();
//        $this->contentTreeObjects = [$this->pageContentTree] + ContentTree::find()->notDeleted()->all();
//        Yii::$app->contentTreeObjects = array_merge([Yii::$app->pageContentTree],
//            Yii::$app->pageContentTree->children()->notHidden()->notDeleted()->all());
    }

    protected function selectBaseModels()
    {
        $idsByTable = ArrayHelper::index(Yii::$app->contentTreeObjects, null, 'table_name');
        foreach ($idsByTable as $tableName => $contentTreeObjects) {
            /** @var BaseModel $className */
            $className = Yii::$app->contentTree->getClassName($tableName);
            $contentTreeIds = ArrayHelper::getColumn($contentTreeObjects, 'record_id');
            $baseModels = $className::find()->with([
                'defaultTranslation',
                'currentTranslation'
            ])->byId($contentTreeIds)->all();
            Yii::$app->baseModelObjects[$tableName] = ArrayHelper::index($baseModels, 'id');
        }
    }

    protected function findContentTreeNormalizeAliasPath($alias, $contentTreeId = null)
    {
        $query = ContentTree::findBySql(
            "select c.*, CONCAT(GROUP_CONCAT(IFNULL(pt.alias, IFNULL(pt2.alias, pt3.alias))
    SEPARATOR '/'), '/', ct.alias) as normilized_alias_path
from content_tree_translation ct
         JOIN content_tree c on ct.content_tree_id = c.id
         LEFT JOIN content_tree par on par.lft < c.lft AND par.rgt > c.rgt
         LEFT JOIN content_tree_translation pt on pt.content_tree_id = par.id AND pt.language = :masterLanguage
         LEFT JOIN content_tree_translation pt2 on pt2.content_tree_id = par.id AND pt2.language = :language
         LEFT JOIN content_tree_translation pt3 on pt3.content_tree_id = par.id AND pt3.language not in (:masterLanguage, :language)
WHERE ct.alias = :alias
    AND par.table_name != :website
GROUP BY c.id",
            [
                'website' => \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE,
                'alias' => $alias,
                'language' => \Yii::$app->language,
                'masterLanguage' => \Yii::$app->websiteMasterLanguage
            ]);
        // @TODO Deal with multiple records
        $result = $query->one();
        return $result;
    }

    /**
     *
     *
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    protected function findContentTreeByFullPath()
    {
        $contentTree = null;
        $aliasPath = Yii::$app->getCurrentAlias();
        $aliasPath = Yii::$app->getCurrentAlias();
        $pageTableName = \intermundia\yiicms\models\ContentTree::TABLE_NAME_PAGE;
        if ($aliasPath) {
            $contentTree = ContentTree::find()
                ->byAliasPath($aliasPath)
                ->byTableName($pageTableName)
                ->notHidden()
                ->notDeleted()
                ->one();
            if (!$contentTree) {
                $alias = substr($aliasPath, strrpos($aliasPath, '/') + 1);
                $contentTree = $this->findContentTreeNormalizeAliasPath($alias);
                if (!$contentTree) {
                    throw new NotFoundHttpException("Requested page was not found");
                }
            }
            return $contentTree;
        }

        if (!($contentTree = ContentTree::find()
            ->byId(Yii::$app->defaultContentId)
            ->byTableName($pageTableName)
            ->notHidden()
            ->notDeleted()
            ->one())) {
            throw new NotFoundHttpException("Content does not exist for [ID = " . Yii::$app->defaultContentId . "]");
        }
//        $this->getView()->contentTreeObject = $contentTree;
        return $contentTree;
    }
}
