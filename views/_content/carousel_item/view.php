<?php

/**
 * @var $this  yii\web\View
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */

use yii\helpers\Html;

?>
<?php echo $this->render('../content-tree/_model_fields_view', [
    'contentTreeModel' => $contentTreeModel,
]); ?>
<?php echo \common\widgets\DetailView::widget([
    'model' => $model,
    'attributes' => [
        'activeTranslation.name',
        'activeTranslation.caption:html',
//        'activeTranslation.status',
//        [
//            'label' => 'Image',
//            'format' => 'html',
//            'value' => Html::img($model->activeTranslation->image->getUrl(), ['style' => 'width: 120px;'])
//        ],
        'created_at:datetime',
    ],
]);

?>
