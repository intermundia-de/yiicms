<?php

/**
 * @var $tableName
 * @var $breadCrumbs
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\BaseTranslateModel
 * @var $contentTreeModel        intermundia\yiicms\models\ContentTree
 * @var $mulitiModel intermundia\yiicms\models\ContentMultiModel
 */

$model = $mulitiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::BASE_TRANSLATION_MODEL);
$contentTreeModel = $mulitiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::CONTENT_TREE_MODEL);

use intermundia\yiicms\models\ContentTree;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;

$this->registerAssetBundle(\intermundia\yiicms\bundle\JSTreeAsset::class);

$title = Yii::t('intermundiacms', 'Update {modelClass}: ', [
        'modelClass' => $model->getModelClassName(),
    ]) . ' ' . $model->getTitle();

$BreadCrumb = [];

foreach ($breadCrumbs as $breadCrumb) {
    $BreadCrumb[] = ['label' => Yii::t('intermundiacms', $breadCrumb['name']), 'url' => $breadCrumb['url']];
}


$BreadCrumb[] = ['label' => Yii::t('intermundiacms', $model->getTitle()), 'url' => $url];
$BreadCrumb[] = Yii::t('intermundiacms', 'Update');
$this->params['breadcrumbs'] = $BreadCrumb;

?>

<?php $form = ActiveForm::begin([
    'enableClientValidation' => true,
    'options' => [
        'class' => 'lobi-form',
        'enctype' => 'multipart/form-data'
    ]
//    'enableAjaxValidation' => true,
]) ?>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<h1><?php echo $title ?></h1>
<?php echo $this->render('../_content/' . $tableName . '/_form', [
    'model' => $model,
    'tableName' => $tableName,
    'contentTreeModel'=>$contentTreeModel,
    'form' => $form
]); ?>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<?php ActiveForm::end() ?>

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
