<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 7:01 PM
 */

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\formatters\SitemapXmlResponseFormatter;
use intermundia\yiicms\models\BaseModel;
use common\models\ContentTree;
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
        $this->selectBaseModels();
        return parent::beforeAction($action);
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $contentTreeItem = Yii::$app->pageContentTree;

        $model = $contentTreeItem->getModel();

        if ($contentTreeItem->record_id !== -1 && !$model) {
            throw new NotFoundHttpException("Content is not editable");
        }

        $this->getView()->contentTreeObject = $contentTreeItem;
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $alias
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $id
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @throws NotFoundHttpException
     */
    protected function selectPageContentTree()
    {
        Yii::$app->pageContentTree = $this->findContentTreeByFullPath();
//        $this->contentTreeObjects = [$this->pageContentTree] + ContentTree::find()->notDeleted()->all();
        Yii::$app->contentTreeObjects = array_merge([Yii::$app->pageContentTree],
            Yii::$app->pageContentTree->children()->notHidden()->notDeleted()->all());
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
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return array|ContentTree|null
     * @throws NotFoundHttpException
     */
    protected function findContentTreeByFullPath()
    {
        $contentTree = null;
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
        $this->getView()->contentTreeObject = $contentTree;
        return $contentTree;
    }


    public function actionSitemapXml($language = '') {
        $websiteLanguages = array_keys(Yii::$app->websiteLanguages);
        if($language) {
            $contentLanguages = array_map(function($lang) {
                $separatorPos = strpos($lang, '-');
                if($separatorPos > -1) {
                    return substr($lang, 0, $separatorPos);
                }
                else {
                    return $lang;
                }
            }, $websiteLanguages);

            if(!in_array($language, $contentLanguages)) {
                throw new NotFoundHttpException();
            }
        }
//        Yii::$app->response->format = Response::FORMAT_XML;
        $siteMapXmlFormatter = new SitemapXmlResponseFormatter();

        Yii::$app->response->format = 'sitemap_xml';
        Yii::$app->response->formatters['sitemap_xml'] = $siteMapXmlFormatter;

        $items = ContentTree::find()
            ->notHidden()
            ->notDeleted()
            ->inSitemap()
            ->andWhere('table_name = :page')
            ->params([
                'page' => ContentTree::TABLE_NAME_PAGE,
            ])
            ->orderBy('lft')
            ->all();

        $sitemapItems = [];
        foreach ($items as $item) {
            $sitemapItems[] = [
                'loc' => $item->getFullUrl(false, true),
                'changefreq' => 'daily',
                'priority' => 1 - ($item->depth - 1) / 10
            ];
        }
        return $sitemapItems;
    }
}
