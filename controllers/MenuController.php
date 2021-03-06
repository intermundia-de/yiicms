<?php

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\models\ContentTreeMenu;
use intermundia\yiicms\web\ContentController;
use Yii;
use intermundia\yiicms\models\Menu;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * MenuController implements the CRUD actions for Menu model.
 */
class MenuController extends ContentController
{

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Menu models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => new ActiveDataProvider([
                'query' => Menu::find()
            ])
        ]);
    }

    /**
     * Displays a single Menu model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Menu model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Menu();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Menu model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionChildren($id)
    {
        $model = $this->findModel($id);

        $contentTreeMenu = ContentTreeMenu::find()
            ->byMenuId($id)
            ->with(['contentTree', 'menu'])
            ->orderBy('position');

        return $this->render('sort', [
            'query' => $contentTreeMenu,
            'model' => $model,
        ]);
    }

    public function actionSort()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $prev = Yii::$app->request->post('prev');
        $next = Yii::$app->request->post('next');
        $element = Yii::$app->request->post('element');
        $transaction = Yii::$app->db->beginTransaction();
        $menuItems = ArrayHelper::index(ContentTreeMenu::find()->byId([$next, $prev, $element])->all(), 'id');
        $elementItem = $menuItems[$element];
        if ($prev && $elementItem->position < $menuItems[$prev]->position) {
            $prevItem = $menuItems[$prev];
            $newPos = $prevItem->position;
            Yii::$app->db
                ->createCommand("UPDATE content_tree_menu SET position=position-1 
                                     WHERE position <= '$prevItem->position' AND 
                                     position > '$elementItem->position' AND 
                                     menu_id = '$elementItem->menu_id'
                                     ")
                ->execute();
            ContentTreeMenu::updateAll(['position' => $newPos], ['id' => $element]);
            $transaction->commit();
            return ['success' => true];
        }


        if ($next && $elementItem->position > $menuItems[$next]->position) {
            $nextItem = $menuItems[$next];
            $newPos = $nextItem->position;
            Yii::$app->db->createCommand("UPDATE content_tree_menu SET position=position+1
                                              WHERE position >= '$nextItem->position' AND
                                              position < '$elementItem->position'AND 
                                              menu_id = '$elementItem->menu_id'
                                         ")
                ->execute();
            ContentTreeMenu::updateAll(['position' => $newPos], ['id' => $element]);
            $transaction->commit();
            return ['success' => true];
        }

        return ['success' => false];
    }

    /**
     * Deletes an existing Menu model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Menu model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Menu the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
