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
use yii\helpers\Url;
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
    const EVENT_BEFORE_SELECT_CONTENT_TREE = 'beforeSelectContentTree';
    const EVENT_AFTER_SELECT_CONTENT_TREE = 'afterSelectContentTree';

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
    public function actionIndex($id = '')
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
        $this->beforeSelectContentTree();
        Yii::$app->pageContentTree = $this->findContentTreeByFullPath();
        $this->afterSelectContentTree();
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
        $pageTableName = \intermundia\yiicms\models\ContentTree::TABLE_NAME_PAGE;
        if ($aliasPath) {
            $contentTreeId = array_search($aliasPath, ContentTree::getIdAliasMap());
            if ($contentTreeId) {
                $contentTree = ContentTree::find()
                    ->byId($contentTreeId)
                    ->notHidden()
                    ->notDeleted()
                    ->one();
            }
            if (!$contentTree) {
                throw new NotFoundHttpException("Requested page was not found");
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

    public function beforeSelectContentTree()
    {
        $this->trigger(self::EVENT_BEFORE_SELECT_CONTENT_TREE);
    }

    public function afterSelectContentTree()
    {
        $this->trigger(self::EVENT_AFTER_SELECT_CONTENT_TREE);
    }
}
