<?php

/**
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\ContentText
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
        'activeTranslation.single_line:html',
        'activeTranslation.multi_line:html',
        'activeTranslation.multi_line2:html',
//        [
//            'label' => 'Image',
//            'format' => 'html',
//            'value' => Html::img($model->activeTranslation->image->getUrl(), ['style' => 'width: 120px;'])
//        ],
        'created_at:datetime'
    ],
]);

?>
