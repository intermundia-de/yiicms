<?php
/*
 * @var $contentTreeModel    intermundia\yiicms\models\ContentTree
 */

?>


<?php echo \common\widgets\DetailView::widget([
    'model' => $contentTreeModel,
    'attributes' => [
        'key',
        'custom_class'
    ]
]); ?>