<?php

/**
 * @var $this  yii\web\View
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */

?>
<?php echo $this->render('../content-tree/_model_fields_view', [
    'contentTreeModel' => $contentTreeModel,
]); ?>
<?php echo \common\widgets\DetailView::widget([
    'model' => $model,
    'attributes' => [
        'activeTranslation.name',
//        'activeTranslation.status',
        'created_at:datetime',
    ],
]);

?>
