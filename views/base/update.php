<?php

/**
 * @var $tableName
 * @var $contentType
 * @var $breadCrumbs
 * @var $this                    yii\web\View
 * @var $model                   intermundia\yiicms\models\BaseTranslateModel
 * @var $contentTreeModel        intermundia\yiicms\models\ContentTree
 * @var $multiModel             intermundia\yiicms\models\ContentMultiModel
 */

$model = $multiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::BASE_TRANSLATION_MODEL);
$contentTreeModel = $multiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::CONTENT_TREE_MODEL);

use yii\helpers\Html;
use yii\bootstrap\Modal;

$this->registerAssetBundle(\intermundia\yiicms\bundle\JSTreeAsset::class);

$this->title = Yii::t('intermundiacms', 'Update {modelClass}: ', [
        'modelClass' => $contentTreeModel->getContentType(),
    ]) . ' ' . $model->getTitle();

$BreadCrumb = [];

foreach ($breadCrumbs as $breadCrumb) {
    $BreadCrumb[] = ['label' => Yii::t('intermundiacms', $breadCrumb['name']), 'url' => $breadCrumb['url']];
}


$BreadCrumb[] = ['label' => Yii::t('intermundiacms', $model->getTitle()), 'url' => ''];
$BreadCrumb[] = Yii::t('intermundiacms', 'Update');
$this->params['breadcrumbs'] = $BreadCrumb;

?>
<?php echo $this->render('_form', [
    'model' => $model,
    'contentTreeModel' => $contentTreeModel,
    'tableName' => $tableName,
    'contentType' => $contentType,
    'url' => ''
]) ?>

<?php
Modal::begin([
    'id' => 'link-plugin-modal',
    'header' => '<h2 style="margin-left: 50px" >Link</h2>',
    'bodyOptions' => ['class' => 'modal-body', 'id' => 'move-modal-body', 'data-key' => 1],
    'size' => 'modal-lg',
    'footer' => Html::button('Link as hyperlink', ['class' => 'btn btn-primary', 'id' => 'link-plugin-button']) . Html::button('Link as object', ['class' => 'btn btn-warning', 'id' => 'link-object-plugin-button']),
]);
?>

<div id="jstree-link-plugin"></div>


<?php
Modal::end();

?>
