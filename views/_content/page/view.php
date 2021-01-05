<?php

/**
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\Page
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
        'activeTranslation.title',
        'activeTranslation.short_description:html',
        'activeTranslation.body:html',
        'activeTranslation.meta_title',
        'activeTranslation.meta_keywords',
        'activeTranslation.meta_description',
//        [
//            'label' => 'Image',
//            'format' => 'html',
//            'value' => Html::img($model->activeTranslation->image->getUrl(), ['style' => 'width: 120px;'])
//        ],
        'created_at:datetime', // creation date formatted as datetime
        'updated_at:datetime',
    ],
]) ;

?>


