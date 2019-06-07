<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 9:25 PM
 */

use yii\helpers\Html;
use yii\helpers\Url;

/** @var $contentTreeItem  \intermundia\yiicms\models\ContentTree */
/** @var $query  \intermundia\yiicms\models\query\ContentTreeQuery */

?>
<div class="content-view">
    <?php
    try {

        echo \yii\grid\GridView::widget([
            'dataProvider' => new \yii\data\ActiveDataProvider([
                'query' => $query,
            ]),
            'tableOptions' => ['class' => 'table table-striped table-bordered', 'id' => 'content_tree_location'],
            'columns' => [
//            ['class' => SerialColumn::class, 'contentOptions' => ['class' => 'not-draggable']],
                [
                    'attribute' => 'id',
                    'contentOptions' => [
                        'class' => 'priority'
                    ],
                    //'content' => function($model){
                    //    /** @var $model \intermundia\yiicms\models\ContentTree */
                    //    return \yii\bootstrap\Html::input('text','sequence',$model->id,['size'=>5]);
                    //}
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Name'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return \yii\bootstrap\Html::a($model->getName(), $model->getFullUrl());
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'View'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return \yii\helpers\ArrayHelper::getValue($model->getViews(), $model->view);
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Show/Hide'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return Html::dropDownList('hide', $model->hide, ['Shown', 'Hidden'], ['class' => 'form-control hide-dropdown']);
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Class'),
                    'format' => 'html',
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return '<i class="fa ' .
                            Yii::$app->contentTree->getIcon($model->table_name, $model->link_id) . '"></i> '
                            . Yii::$app->contentTree->getDisplayName($model->table_name);
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Full Alias'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return $model->activeTranslation->alias_path;
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Modifier'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return $model->getUpdatedByUsername();
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Modified at'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return Yii::$app->formatter->format($model->updated_at, 'datetime');
                    },
                    'contentOptions' => ['class' => 'not-draggable'],
                ],
                [
                    'label' => Yii::t('intermundiacms', 'Published at'),
                    'content' => function ($model) {
                        /** @var $model \intermundia\yiicms\models\ContentTree */
                        return Yii::$app->formatter->format($model->created_at, 'datetime');
                    },
                    'contentOptions' => ['class' => 'not-draggable']
                ],
            ]
        ]);

    } catch (Exception $e) {
        throw $e;
    }
    ?>
</div>
