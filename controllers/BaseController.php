<?php

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\commands\AddToTimelineCommand;
use intermundia\yiicms\helpers\CopyTranslation;
use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\BaseTranslateModel;
use intermundia\yiicms\models\ContentTreeMenu;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\ContentMultiModel;
use intermundia\yiicms\models\FileManagerItem;
use intermundia\yiicms\models\TimelineEvent;
use intermundia\yiicms\web\BackendController;
use intermundia\yiicms\models\User;
use intermundia\yiicms\models\UserToken;
use intermundia\yiicms\traits\FormAjaxValidationTrait;
use intermundia\yiicms\models\ContentTree;
use common\base\MultiModel;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

class BaseController extends BackendController
{
    use FormAjaxValidationTrait;

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                ],
            ],
        ];
    }

    /**
     *
     *
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function beforeAction($action)
    {
        if ($action->id == 'menu') {
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action);
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect(['/content-tree/index']);
    }

    /**
     * @param $contentType
     * @param $parentContentId
     * @param $language
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     * @var  MultiModel $model
     */
    public function actionCreate($contentType, $parentContentId, $language)
    {
        /** @var BaseModel $baseModelClassName */
        $baseModelClassName = Yii::$app->contentTree->getClassName($contentType);
        $tableName = $baseModelClassName::getFormattedTableName();
        $baseTranslateModelClassName = $baseModelClassName::getTranslateModelClass();

        $contentTree = new ContentTree();
        $contentTree->content_type = $contentType;

        $model = new ContentMultiModel([
            'models' => [
                ContentMultiModel::BASE_MODEL => new $baseModelClassName(),
                ContentMultiModel::BASE_TRANSLATION_MODEL => new $baseTranslateModelClassName(),
                ContentMultiModel::CONTENT_TREE_MODEL => $contentTree
            ]
        ]);

        $model->getBaseTranslationModel()->parentContentId = $parentContentId;
        $model->getBaseTranslationModel()->language = $language;


        $transaction = Yii::$app->db->beginTransaction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if (intval(Yii::$app->request->post('go_to_parent'))) {
                $tree = ContentTree::find()->byId($parentContentId)->one();
            } else {
                $foreignKey = $model->getModel('baseTrasnlationModel')->getForeignKeyNameOnModel();
                $tree = ContentTree::find()->byRecordIdTableName($model->getBaseTranslationModel()->$foreignKey,
                    $tableName)->one();
            }
            $transaction->commit();
            return $this->redirect($tree->getFullUrl());
        }
        $transaction->rollBack();
        $tree = ContentTree::find()->byId($parentContentId)->one();

        $breadCrumbs = $tree->getBreadCrumbs();
        return $this->render(
            'create', [
            'multiModel' => $model,
            'tableName' => $tableName,
            'contentType' => $contentType,
            'breadCrumbs' => $breadCrumbs,
            'url' => $tree->getFullUrl()
        ]);
    }

    /**
     * @param $contentType
     * @param $parentContentId
     * @param $contentId
     * @param $language
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($contentType, $parentContentId, $contentId, $language)
    {
        $baseModelClassName = Yii::$app->contentTree->getClassName($contentType);
        $tableName = $baseModelClassName::getFormattedTableName();
        $baseTranslateModelClassName = $baseModelClassName::getTranslateModelClass();
        $translateModel = new $baseTranslateModelClassName();
        $baseTranslationModel = $this->findModel($baseModelClassName, $translateModel, $contentId, $language);

        $model = new ContentMultiModel([
            'models' => [
                ContentMultiModel::BASE_MODEL => $baseTranslationModel->baseModel,
                ContentMultiModel::BASE_TRANSLATION_MODEL => $baseTranslationModel,
                ContentMultiModel::CONTENT_TREE_MODEL => $baseTranslationModel->contentTree,
            ]
        ]);

        $model->getBaseTranslationModel()->parentContentId = $parentContentId;
        $model->getBaseTranslationModel()->language = $language;

        $transaction = Yii::$app->db->beginTransaction();
        $request = Yii::$app->request;
        if ($model->load($request->post()) && $model->save()) {
            $transaction->commit();
            if (intval($request->post('go_to_parent'))) {
                $tree = ContentTree::find()->byId($parentContentId)->one();
                return $this->redirect($tree->getFullUrl());
            }
            if (intval($request->post('stay_here'))) {
                return $this->refresh();
            }
            $tree = ContentTree::find()->byRecordIdTableName($contentId, $tableName)->one();
            return $this->redirect($tree->getFullUrl());
        }

        $transaction->rollBack();
        $tree = ContentTree::find()->byId($parentContentId)->one();
        $breadCrumbs = $tree ? $tree->getBreadCrumbs() : [];
        return $this->render(
            'update', [
            'multiModel' => $model,
            'tableName' => $tableName,
            'contentType' => $contentType,
            'breadCrumbs' => $breadCrumbs,
            'url' => $tree ? $tree->getFullUrl() : '/'
        ]);
    }

    /**
     * @param $tableName
     * @param $id
     * @param $from
     * @param $to
     * @return \yii\web\Response
     */
    public function actionAddNewLanguage()
    {
        if (Yii::$app->request->isPost) {
            $post = Yii::$app->request->post('DynamicModel');
            if (!$post) {
                throw new InvalidArgumentException();
            }
            $tableName = $post['tableName'];
            $id = $post['id'];
            $to = $post['to'];
            $from = $post['from'];
            if (!($tableName || $id || $to)) {
                throw new InvalidArgumentException();
            }


            $className = Yii::$app->contentTree->getClassName($tableName);
            /** @var $baseModel BaseModel */
            $baseModel = $className::find()->byId($id)->one();

            if (!$from) {
                return $this->redirect($baseModel->getUpdateUrlByLanguage($to));
            }

            $contentTree = ContentTree::find()->byTableName($tableName)->byRecordId($id)->notDeleted()->one();

            $transaction = Yii::$app->db->beginTransaction();

            $copyTranslations = new CopyTranslation($from, $to, $contentTree);
            $copyTranslations->copyAll();

            $transaction->commit();
            return $this->redirect($copyTranslations->getBaseModel()->getUpdateUrlByLanguage($to));


            $transaction->rollBack();

        }
    }

    /**
     * @param $tableName
     * @param $contentTreeId
     * @param $id
     * @return \yii\web\Response
     * @throws \trntv\bus\exceptions\MissingHandlerException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     */
    public function actionDelete($tableName, $contentTreeId, $id)
    {
        $ClassName = Yii::$app->contentTree->getClassName($tableName);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        /** @var BaseModel $baseModel */
        $baseModel = $ClassName::find()->byId($id)->one();
        $tree = ContentTree::find()->byId($contentTreeId)->one();
        $parent = $tree->getParent();
        $recordName = $tree->getActualItemActiveTranslation()->name;
        $oldData = $baseModel->activeTranslation->getData();

        $tree->deleteWithBaseModel();
//        $tree->deleted_by = Yii::$app->user->id;
//
//        if (!$tree->link_id) {
//            $baseModel->deleted_at = time();
//            $baseModel->deleted_by = Yii::$app->user->id;
//            $baseModel->save();
//        }
//
//        $children = $tree->children()->notDeleted()->all();
//        foreach ($children as $child) {
//            if (!$child->link_id) {
//                /** @var BaseModel $childBaseModel */
//                $ClassName = Yii::$app->contentTree->getClassName($child->table_name);
//                $childBaseModel = $ClassName::find()->byId($child->record_id)->one();
//                $childBaseModel->deleted_at = time();
//                $childBaseModel->deleted_by = Yii::$app->user->id;
//                $childBaseModel->save();
//            }
//            $child->deleted_at = time();
//            $child->deleted_by = Yii::$app->user->id;
//            $child->save();
//        }
//
//        $tree->save();
        $transaction->commit();
        $category = $ClassName::getFormattedTableName();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'group' => TimelineEvent::GROUP_CONTENT,
            'category' => $category,
            'event' => TimelineEvent::EVENT_DELETE,
            'record_id' => $id,
            'record_name' => $recordName,
            'data' => ['old' => $oldData],
            'createdBy' => Yii::$app->user->id
        ]));

        return $this->redirect($parent->getFullUrl());
    }

    /**
     * @param $tableName
     * @param $contentId
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionArchive($tableName, $contentId)
    {
        $ClassName = Yii::$app->contentTree->getClassName($tableName);
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            /** @var BaseModel $baseModel */
            $baseModel = $ClassName::find()->byId($contentId)->one();
            $baseModel->deleted_at = time();
            $baseModel->save();
            $tree = ContentTree::find()->byRecordIdTableName($contentId, $tableName)->one();
            $tree->deleted_at = time();
            $tree->deleted_by = Yii::$app->user->id;
            $tree->save();

            $transaction->commit();
            $tableName = $ClassName::tableName();
            $category = str_replace('}}', '', str_replace('{{%', '', $tableName));
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'group' => TimelineEvent::GROUP_CONTENT,
                'category' => $category,
                'event' => TimelineEvent::EVENT_ARCHIVE,
                'record_id' => $contentId,
                'record_name' => $baseModel->getTitle(),
                'data' => ['old' => $baseModel->activeTranslation->getData()],
                'createdBy' => Yii::$app->user->id
            ]));
        } catch (\Exception $e) {
            $transaction->rollBack();
        }

        return $this->redirect($tree->getFullUrl());
    }

    public function actionSwap()
    {
        $request = Yii::$app->request;
        $prev = intval($request->post('prev'));
        $next = intval($request->post('next'));
        $element = intval($request->post('element'));

        if ($element > 0 && $tree = ContentTree::find()->byId($element)->one()) {
            if ($prev > 0 && $prevTree = ContentTree::find()->byId($prev)->one()) {
                if ($tree->insertAfter($prevTree)) {
                    $this->swapTimelineEvent($tree);
                    return true;
                }
            } else {
                if ($next > 0 && $nextTree = ContentTree::find()->byId($next)->one()) {
                    if ($tree->insertBefore($nextTree)) {
                        $this->swapTimelineEvent($tree);
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @return bool
     * @throws \trntv\bus\exceptions\MissingHandlerException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionUpdateView()
    {
        $id = intval(Yii::$app->request->post('id'));
        $view = Yii::$app->request->post('value');

        $tree = ContentTree::find()->byId($id)->one();

        if ($tree) {
            $data = ['old' => ['view' => $tree->view ? $tree->view : Yii::t('intermundiacms', 'Default')]];

            if (Yii::$app->contentTree->viewExists($tree->content_type, $view)) {
                $tree->view = $view;
            } else {
                $tree->view = null;
            }
            $data['new'] = ['view' => $tree->view ? $tree->view : Yii::t('intermundiacms', 'Default')];
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'group' => TimelineEvent::GROUP_CONTENT,
                'category' => TimelineEvent::CATEGORY_CONTENT_TREE,
                'event' => TimelineEvent::EVENT_DESIGN_CHANGE,
                'record_id' => $id,
                'record_name' => $tree->getActualItemActiveTranslation()->name,
                'data' => $data,
                'createdBy' => Yii::$app->user->id
            ]));
            return $tree->save();
        }
        return false;
    }

    /**
     * @return bool
     * @throws \trntv\bus\exceptions\MissingHandlerException
     * @throws \yii\base\InvalidConfigException
     */

    public function actionUpdateHide()
    {
        $id = intval(Yii::$app->request->post('id'));
        $hide = intval(Yii::$app->request->post('value'));

        $tree = ContentTree::find()->byId($id)->one();

        if ($tree) {
            $data = ['old' => ['hide' => $tree->hide ? $tree->hide : Yii::t('intermundiacms', 'Default')]];

            $tree->hide = $hide;

            $data['new'] = ['hide' => $tree->hide ? $tree->hide : Yii::t('intermundiacms', 'Default')];
            Yii::$app->commandBus->handle(new AddToTimelineCommand([
                'group' => TimelineEvent::GROUP_CONTENT,
                'category' => TimelineEvent::CATEGORY_CONTENT_TREE,
                'event' => $hide == 1 ? TimelineEvent::SHOW : TimelineEvent::HIDE,
                'record_id' => $id,
                'record_name' => $tree->getActualItemActiveTranslation()->name,
                'data' => $data,
                'createdBy' => Yii::$app->user->id
            ]));
            return $tree->save();
        }
        return false;
    }

    public function actionMenu()
    {
        $this->enableCsrfValidation = false;


        if (Yii::$app->request->isPost) {
            $showInMenu = array_map('intval', Yii::$app->request->post('menu_ids', []));

            $id = intval(Yii::$app->request->post('id'));
            $tree = ContentTree::find()->byId($id)->linkedIdIsNull()->one();
            if ($tree && $menuModel = ContentTreeMenu::find()->byContentTreeId($tree->id)->all()) {
                $menu = ArrayHelper::map($menuModel, 'menu_id', 'menu_id');
                $del = [];

                foreach (array_diff($menu, $showInMenu) as $deleted) {
                    array_push($del, $deleted);
                }

                $deletedItems = ContentTreeMenu::find()->byMenuId($del)->byContentTreeId($tree->id)->all();

                foreach ($deletedItems as $deletedItem) {
                    Yii::$app->db
                        ->createCommand("UPDATE content_tree_menu SET position=position-1 
                                     WHERE position > '$deletedItem->position' AND 
                                     menu_id = '$deletedItem->menu_id'
                                     ")
                        ->execute();
                }
                ContentTreeMenu::deleteAll(['content_tree_id' => $tree->id, 'menu_id' => $del]);
                $add = [];

                foreach (array_diff($showInMenu, $menu) as $added) {
                    array_push($add, $added);
                }

                foreach ($add as $menuItem) {
                    $newPosition = ContentTreeMenu::find()->byMenuId($menuItem)->orderBy(['position' => SORT_DESC])->one();
                    $menuModel = new ContentTreeMenu();
                    $menuModel->menu_id = intval($menuItem);
                    $menuModel->content_tree_id = $tree->id;
                    $menuModel->position = $newPosition ? $newPosition->position + 1 : 1;
                    if (!$menuModel->save()) {
                        return false;
                    }
                }
            } else {
                if ($tree) {
                    foreach ($showInMenu as $menuItem) {
                        $newPosition = ContentTreeMenu::find()->byMenuId($menuItem)->orderBy(['position' => SORT_DESC])->one();
                        $menuModel = new ContentTreeMenu();
                        $menuModel->menu_id = $menuItem;
                        $menuModel->content_tree_id = $tree->id;
                        $menuModel->position = $newPosition ? $newPosition->position + 1 : 1;
                        if (!$menuModel->save()) {
                            return false;
                        }
                    }
                } else {
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionTree()
    {
        $id = intval(Yii::$app->request->post('key', 0));
        $tableNames = (Yii::$app->request->post('table_names', null));

        $tree = ContentTree::getItemsAsTreeForLink($id, $tableNames);
        return json_encode($tree);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function actionTreeForMove()
    {
        $id = intval(Yii::$app->request->post('key', 0));
        $tableNames = (Yii::$app->request->post('table_names', null));

        $tree = ContentTree::getItemsAsTreeForLink($id, $tableNames);
        return json_encode($tree);
    }

    /**
     * @param $id
     * @param $contentTreeId
     * @return \yii\web\Response
     * @throws \yii\base\Exception
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUserLogin($id, $contentTreeId)
    {
        $model = $this->findUserModel($id);
        $tokenModel = UserToken::create(
            $model->getId(),
            UserToken::TYPE_LOGIN_PASS,
            60
        );

        return $this->redirect(
            Yii::$app->urlManagerFrontend->createAbsoluteUrl([
                'user/sign-in/login-by-pass',
                'token' => $tokenModel->token,
                'contentTreeId' => $contentTreeId
            ])
        );
    }

    /**
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionLinkTree()
    {
        $res = ['code' => 1];
        $treeId = intval(Yii::$app->request->post()['tree']);
        $parentTree = ContentTree::find()->byId($treeId)->one();
        $parentTreeTranslations = ArrayHelper::index($parentTree->translations, 'language');
        if ($parentTree) {
            if (isset(Yii::$app->request->post()['ids']) && is_array(Yii::$app->request->post()['ids'])) {
                foreach (Yii::$app->request->post()['ids'] as $id) {
                    $linkedParentTree = ContentTree::find()->byId(intval($id))->one();
                    if ($linkedParentTree) {
                        $linkedTree = new ContentTree();
                        $linkedTree->link_id = $linkedParentTree->id;
                        $linkedTree->record_id = $linkedParentTree->record_id;
                        $linkedTree->table_name = $linkedParentTree->table_name;
                        $linkedTree->content_type = $linkedParentTree->content_type;
                        $transaction = Yii::$app->db->beginTransaction();
                        if (!$linkedTree->appendTo($parentTree)) {
                            return json_encode(['code' => 0, 'message' => 'Could Not Prepend To Parent Node']);
                        } else {
                            foreach ($linkedParentTree->translations as $linkedParentTreeTranslation) {
                                if (!isset($parentTreeTranslations[$linkedParentTreeTranslation->language])) {
                                    continue;
                                }
                                $data = ArrayHelper::toArray($linkedParentTreeTranslation);
                                $parentTreeTranslation = $parentTreeTranslations[$linkedParentTreeTranslation->language];
                                unset($data['id']);
                                $linkedTreeTranslation = new ContentTreeTranslation();
                                $linkedTreeTranslation->load($data, '');
                                $linkedTreeTranslation->content_tree_id = $linkedTree->id;
                                $linkedTreeTranslation->alias_path = $parentTreeTranslation->alias_path . '/' . $linkedTreeTranslation->alias;
                                $linkedTreeTranslation->getBehavior('sluggable')->onlyMakeUniqueInPath = true;
                                if (!$linkedTreeTranslation->save()) {
                                    $transaction->rollBack();
                                    return json_encode(['code' => 0, 'message' => 'Could Not Save In Translation']);
                                }
                            }
                            $transaction->commit();
                        }
                    } else {
                        $res = ['code' => 0, 'message' => 'Tree Not Found'];
                    }
                }
            }
        } else {
            $res = ['code' => 0, 'message' => 'Tree Not Found'];
        }
        return json_encode($res);
    }


    public function actionMoveTree()
    {
        $res = ['code' => 1];
        $request = \Yii::$app->request;
        $transaction = Yii::$app->db->beginTransaction();
        $appendToTree = ContentTree::find()->byId($request->post('prepend_to'))->one();
        if ($request->post('moved') && $appendToTree) {
            foreach ($request->post('moved') as $moved) {
                $movedTree = ContentTree::find()->byId(intval($moved))->one();
                if ($movedTree && $movedTree->appendTo($appendToTree)) {
                    $newMovedTree = ContentTree::find()->byId(intval($moved))->one();
                    foreach ($newMovedTree->translations as $translation) {
                        $translation->move = $newMovedTree->depth - $movedTree->depth;
                        if (!$translation->save()) {
                            $transaction->rollBack();
                            return [
                                'code' => 0,
                                'message' => 'Could not save transalation for language: ' . $translation->language
                            ];
                        }
                    }
                    $transaction->commit();
                } else {
                    $res = ['code' => 0, 'message' => 'Tree Not Found'];
                }
            }
        } else {
            $res = ['code' => 0, 'message' => 'Bad Request'];
        }

        return json_encode($res);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findUserModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param $basicModelClass
     * @param $translateModel
     * @param $contentId
     * @param $language
     * @return BaseTranslateModel | null
     * @throws NotFoundHttpException
     */
    protected function findModel($basicModelClass, $translateModel, $contentId, $language)
    {
        if (($model = $translateModel::find()->findByObjectIdAndLanguage($contentId, $language,
                $translateModel->getForeignKeyNameOnModel())->one()) !== null) {
            return $model;
        } else {
            if (($model = $basicModelClass::find()->byId($contentId)->one()) !== null) {
                $key = $translateModel->getForeignKeyNameOnModel();
                $translateModel->$key = intval($contentId);
                return $translateModel;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }
    }

    /**
     * @param \intermundia\yiicms\models\ContentTree $tree
     * @throws \trntv\bus\exceptions\MissingHandlerException
     * @throws \yii\base\InvalidConfigException
     */
    private function swapTimelineEvent($tree)
    {
        $category = $tree->table_name;
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'group' => TimelineEvent::GROUP_CONTENT,
            'category' => $category,
            'event' => TimelineEvent::EVENT_POSITION,
            'record_id' => $tree->record_id,
            'record_name' => $tree->getActualItem()->activeTranslation->name,
            'data' => ['alias' => $tree->getActualItem()->activeTranslation->alias],
            'createdBy' => Yii::$app->user->id
        ]));
    }

    public function actionDeleteFileItem()
    {
        return true;
    }

    private function modifyBlameData($data)
    {
        $modifiedData = $data;
        if (isset($modifiedData['created_at'])) {
            $modifiedData['created_at'] = time();
        }
        if (isset($modifiedData['created_by'])) {
            $modifiedData['created_by'] = Yii::$app->user->id;
        }
        if (isset($modifiedData['updated_at'])) {
            $modifiedData['updated_at'] = time();
        }
        if (isset($modifiedData['updated_by'])) {
            $modifiedData['updated_by'] = Yii::$app->user->id;
        }
        if (isset($modifiedData['deleted_at']) && $modifiedData['deleted_at']) {
            $modifiedData['updated_at'] = time();
            $modifiedData['deleted_by'] = Yii::$app->user->id;
        }

        return $modifiedData;

    }

    /**
     * @param $baseModelId
     * @param $from
     * @param $to
     * @param $tableName
     * @return bool
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     */
    private function copyFileManagerItems($baseModelId, $from, $to, $tableName, $newAliasPath)
    {
        /** copy filemanager items */
        $fileManagerItems = FileManagerItem::find()
            ->byRecordId($baseModelId)
            ->byLanguage($from)
            ->byTable($tableName)
            ->asArray()
            ->all();

        $fileManagerData = [];
        $oldPath = '';
        $newPath = '';
        if ($fileManagerItems) {
            $copyToDir = Yii::getAlias(FileManagerItem::STORAGE_PATH . "$to/$newAliasPath");
            FileHelper::createDirectory($copyToDir, 0775, true);
        }
        foreach ($fileManagerItems as $fileManagerItem) {
            unset($fileManagerItem['id']);
            $oldPath = $fileManagerItem['path'];
            $fileName = preg_replace('/^.*\/\s*/', '', $oldPath);
            $fileManagerItem['record_id'] = $baseModelId;
            $fileManagerItem['language'] = $to;
            $fileManagerItem['path'] = "$to/$newAliasPath/$fileName";
            $fileManagerItem = $this->modifyBlameData($fileManagerItem);
            $fileManagerData[] = $fileManagerItem;
            $copyFromFile = Yii::getAlias(FileManagerItem::STORAGE_PATH . $oldPath);
            $copyToFile = $copyToDir . '/' . $fileName;
            if (file_exists($copyFromFile) && copy($copyFromFile, $copyToFile)) {
                Yii::$app->db->createCommand()->batchInsert(FileManagerItem::tableName(), array_keys($fileManagerItem),
                    [$fileManagerItem])->execute();
            }
        }

        return true;

    }
}
