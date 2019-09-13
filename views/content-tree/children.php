<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 9:25 PM
 */

use intermundia\yiicms\helpers\LanguageHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var $contentTreeItem  \intermundia\yiicms\models\ContentTree */
/** @var $query  \intermundia\yiicms\models\query\ContentTreeQuery */
/** @var $this  \intermundia\yiicms\web\View */

$assetBundle = $this->registerAssetBundle(\intermundia\yiicms\bundle\ChildrenTreeAsset::class);
?>

    <br>
<?php
echo \yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $query,
        'pagination' => [
            'pageSize' => 500
        ]
    ]),
    'tableOptions' => ['class' => 'table table-striped table-bordered', 'id' => 'content_tree_child'],
    'columns' => [
        [
            'label' => '',
            'content' => function () {
                return '<div style="cursor: pointer" class="pointer glyphicon glyphicon-align-justify"></div>';
            },
            'contentOptions' => ['class' => 'tree-children-draggable']
        ],
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
                if ($model->hasCustomViews()) {
                    return Html::dropDownList('view', $model->view, $model->getViews(),
                        ['class' => 'form-control view-dropdown']);
                } else {
                    return null;
                }
            },
            'contentOptions' => ['class' => 'not-draggable'],
        ],
        [
            'label' => Yii::t('intermundiacms', 'Show/Hide'),
            'content' => function ($model) {
                /** @var $model \intermundia\yiicms\models\ContentTree */
                return Html::dropDownList('hide', $model->hide, ['Shown', 'Hidden'],
                    ['class' => 'form-control hide-dropdown']);
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
                    . Yii::$app->contentTree->getDisplayName($model->content_type);
            },
            'contentOptions' => ['class' => 'not-draggable'],
        ],
//        [
//            'label' => Yii::t('intermundiacms', 'Alias'),
//            'content' => function ($model) {
//                /** @var $model \intermundia\yiicms\models\ContentTree */
//                return $model->getActualItemActiveTranslation()->alias;
//            },
//            'contentOptions' => ['class' => 'not-draggable'],
//        ],
        [
            'label' => Yii::t('intermundiacms', 'Modifier'),
            'content' => function ($model) {
                /** @var $model \intermundia\yiicms\models\ContentTree */
                return $model->updatedBy->username;
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
        [
            'label' => Yii::t('intermundiacms', 'Translations'),
            'content' => function ($model) use ($assetBundle) {
                /** @var $model \intermundia\yiicms\models\ContentTree */
                return implode(' ', array_map(function($langCode) use ($assetBundle) {
                    return Html::img($assetBundle->baseUrl.'/flags/'.LanguageHelper::convertLongCodeIntoShort($langCode).'.png', [
                            'style' => 'width: 20px;'
                    ]);
                }, ArrayHelper::getColumn($model->translations, 'language')));
            },
            'contentOptions' => ['class' => 'not-draggable']
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {actions}',
            'contentOptions' => ['class' => 'not-draggable'],
            'buttonOptions' => [
                'class' => 'btn btn-pretty btn-info'
            ],
            'urlCreator' => function ($action, $contentTreeItem) {
                /** @var \intermundia\yiicms\models\ContentTree $contentTreeItem */
                /** @var \intermundia\yiicms\models\BaseModel $model */
                $model = $contentTreeItem->getModel();
                if ($action === 'view') {
                    return [
                        'content-tree/index',
                        'nodes' => $contentTreeItem->getActualItemActiveTranslation()->alias_path
                    ];
                } else {
                    if ($action === 'update') {
                        return $model->getUpdateUrl();
                    } else {
                        if ($action === 'edit') {
                            return ['content-tree/update', 'id' => $contentTreeItem->id];
                        }
                    }
                }
            },
            'buttons' => [
                'actions' => function ($url, $contentTreeItem) {
                    /** @var \intermundia\yiicms\models\ContentTree $contentTreeItem */
                    /** @var \intermundia\yiicms\models\BaseModel $model */
                    $model = $contentTreeItem->getModel();

                    return \yii\bootstrap\ButtonDropdown::widget([
                        'label' => '<span class="glyphicon glyphicon-cog"></span>',
                        'encodeLabel' => false,
                        'options' => [
                            'class' => 'btn btn-pretty btn-info'
                        ],
                        'dropdown' => [
                            'options' => [
                                'class' => 'dropdown-menu-right'
                            ],
                            'items' => [
                                [
                                    'label' => Yii::t('intermundiacms', 'Move to'),
                                    'url' => '#move-modal',
                                    'encode' => false,
                                    'linkOptions' => [
                                        'data' => [
                                            'toggle' => 'modal',
                                            'target' => '#move-modal',
                                            'key' => $contentTreeItem->id
                                        ]
                                    ]
                                ],
                                [
                                    'label' => Yii::t('intermundiacms', 'Link to'),
                                    'url' => '#linked',
                                    'encode' => false,
                                    'linkOptions' => [
                                        'data' => [
                                            'toggle' => 'modal',
                                            'target' => '#linked',
                                            'key' => $contentTreeItem->id
                                        ]
                                    ]
                                ],
                                [
                                    'label' => Yii::t('intermundiacms', 'Link under'),
                                    'url' => '#linked',
                                    'encode' => false,
                                    'linkOptions' => [
                                        'data' => [
                                            'toggle' => 'modal',
                                            'target' => '#linked',
                                            'key' => $contentTreeItem->id
                                        ]
                                    ]
                                ],
                                [
                                    'label' => Yii::t('intermundiacms', 'Delete'),
                                    'url' => $model->getDeleteUrl($contentTreeItem->id),
                                    'encode' => false,
                                    'linkOptions' => [
                                        'data' => [
                                            'method' => 'post',
                                            'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]);
                }
            ]
        ],
    ]
]);

