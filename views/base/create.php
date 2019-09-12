<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 6/19/18
 * Time: 8:55 PM
 */

use intermundia\yiicms\bundle\JSTreeAsset;
use intermundia\yiicms\models\ContentMultiModel;
use yii\helpers\Html;
use yii\bootstrap\Modal;

/**
 * @var $tableName
 * @var $contentType
 * @var $breadCrumbs
 * @var $this                     yii\web\View
 * @var $dataProvider             yii\data\ActiveDataProvider
 * @var $model                    intermundia\yiicms\models\BaseTranslateModel
 * @var $contentTreeModel         intermundia\yiicms\models\ContentTree
 * @var $parentContentTree        intermundia\yiicms\models\ContentTree
 * @var $multiModel               intermundia\yiicms\models\ContentMultiModel
 */

$baseModel = $multiModel->getModel(ContentMultiModel::BASE_MODEL);
$model = $multiModel->getModel(ContentMultiModel::BASE_TRANSLATION_MODEL);
$contentTreeModel = $multiModel->getModel(ContentMultiModel::CONTENT_TREE_MODEL);
$this->registerAssetBundle(JSTreeAsset::class);

$displayName = Yii::$app->contentTree->getDisplayName($contentTreeModel->content_type);
$this->title = Yii::t('intermundiacms', 'Create new {modelClass}', [
        'modelClass' => '"' . $displayName . '"',
    ]) . ' ' . $model->getTitle();

$BreadCrumb = [];

foreach ($breadCrumbs as $breadCrumb) {
    $BreadCrumb[] = ['label' => Yii::t('intermundiacms', $breadCrumb['name']), 'url' => $breadCrumb['url']];
}

$BreadCrumb[] = ['label' => Yii::t('intermundiacms', $displayName), 'url' => ''];
$BreadCrumb[] = Yii::t('intermundiacms', 'Create');
$this->params['breadcrumbs'] = $BreadCrumb;
?>
<?php echo $this->render('_form', [
    'model' => $model,
    'baseModel' => $baseModel,
    'contentTreeModel' => $contentTreeModel,
    'tableName' => $tableName,
    'contentType' => $contentType,
    'url' => $parentContentTree->getUrl()
]) ?>

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
