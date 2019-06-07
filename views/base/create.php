<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 6/19/18
 * Time: 8:55 PM
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\bootstrap\Modal;

/**
 * @var $tableName
 * @var $breadCrumbs
 * @var $this         yii\web\View
 * @var $dataProvider yii\data\ActiveDataProvider
 * @var $model        intermundia\yiicms\models\BaseTranslateModel
 * @var $contentTreeModel        intermundia\yiicms\models\ContentTree
 * @var $multiModel        intermundia\yiicms\models\ContentMultiModel
 */

$model = $mulitiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::BASE_TRANSLATION_MODEL);
$contentTreeModel = $mulitiModel->getModel(\intermundia\yiicms\models\ContentMultiModel::CONTENT_TREE_MODEL);
$this->registerAssetBundle(\intermundia\yiicms\bundle\JSTreeAsset::class);

$title = Yii::t('intermundiacms', 'Create {modelClass}', [
        'modelClass' => $model->getModelClassName(),
    ]) . ' ' . $model->getTitle();

$BreadCrumb = [];

foreach ($breadCrumbs as $breadCrumb) {
    $BreadCrumb[] = ['label' => Yii::t('intermundiacms', $breadCrumb['name']), 'url' => $breadCrumb['url']];
}

$BreadCrumb[] = ['label' => Yii::t('intermundiacms', $model->getModelClassName()), 'url' => ''];
$BreadCrumb[] = Yii::t('intermundiacms', 'Create');
$this->params['breadcrumbs'] = $BreadCrumb;
?>
<?php $form = ActiveForm::begin([
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
//    'enableAjaxValidation' => true,
]) ?>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<h1><?php echo $title ?></h1>
<?php echo $this->render('../_content/' . $tableName . '/_form', [
    'model' => $model,
    'contentTreeModel'=>$contentTreeModel,
    'tableName' => $tableName,
    'form' => $form
]); ?>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<?php echo Html::hiddenInput('go_to_parent','0',['id' => 'idGoToParent']); ?>
<?php ActiveForm::end() ?>

<?php
Modal::begin([
    'id' => 'link-plugin-modal',
    'header' => '<h2 style="margin-left: 50px" >Link</h2>',
    'bodyOptions' => ['class' => 'modal-body', 'id' => 'move-modal-body', 'data-key' => 1],
    'size' => 'modal-lg',
    'footer' => Html::button('Link', ['class' => 'btn btn-primary', 'id' => 'link-plugin-button']),
]);
?>

    <div id="jstree-link-plugin"></div>


<?php
Modal::end();

?>
