<?php

/**
 * User: mirian
 * Date: 29/07/19
 * Time: 4:55 PM
 */

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\web\BackendController;
use intermundia\yiicms\models\search\TimelineEventSearch;
use Yii;

/**
 * Application timeline controller
 */
class TimelineEventController extends BackendController
{
    public $layout = 'common';

    /**
     * Lists all TimelineEvent models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TimelineEventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => ['created_at' => SORT_DESC]
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
