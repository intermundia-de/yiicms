<?php

/**
 * User: zura
 * Date: 6/19/18
 * Time: 7:01 PM
 */

use yii\bootstrap\ButtonDropdown;
use yii\bootstrap\Modal;
use yii\bootstrap\Tabs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var $this \yii\web\View */
/** @var $contentTreeItem  \intermundia\yiicms\models\ContentTree */
/** @var $model  \intermundia\yiicms\models\BaseModel */
$this->registerAssetBundle(\intermundia\yiicms\bundle\JSTreeAsset::class);

$model = $contentTreeItem->getModel();
$checked = $contentTreeItem->getMenuTreeModel();
$menus = \intermundia\yiicms\models\Menu::find()->all();
$locationQuery = $contentTreeItem->getLinkLocationParents();

$this->title = Yii::t('intermundiacms', 'View {modelClass}: ', [
        'modelClass' => $contentTreeItem->getContentType(),
    ]) . ' ' . $model->getTitle();

$BreadCrumb = [];

foreach ($contentTreeItem->getBreadCrumbs() as $breadCrumb) {
    $BreadCrumb[] = ['label' => Yii::t('intermundiacms', $breadCrumb['name']), 'url' => $breadCrumb['url']];
}

$BreadCrumb[] = Yii::t('intermundiacms', 'View');
$this->params['breadcrumbs'] = $BreadCrumb;
$tableNames = array_map(function ($tableName) {
    return $tableName['displayName'];
}, Yii::$app->contentTree->getEditableClassesKey());

unset($tableNames['website']);
$tableNames['all'] = 'All';
?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" type="text/css"
          rel="stylesheet"/>

    <div class="well well-sm">

        <form action="<?php echo Url::to(['/base/menu']) ?>" method="post" id="show_in_menu" class="form-inline"
              style="display: inline-block">
            <input type="hidden" name="id" value="<?= $contentTreeItem->getTreeId() ?>">

            <?php echo Html::checkboxList('menu_ids[]', array_keys($checked), \yii\helpers\ArrayHelper::map($menus, 'id', 'name')); ?>
        </form>
    </div>

<?php

$view = $this->render('view', [
    'model' => $model,
    'contentTreeItem' => $contentTreeItem
]);

$location = $this->render('location', [
    'query' => $locationQuery,
    'contentTreeItem' => $contentTreeItem
]);


$items = [
    [
        'label' => Yii::t('intermundiacms', 'View'),
        'content' => $view,
    ],
    [
        'label' =>
            Yii::t('intermundiacms', 'Location') .
            ' (' . $locationQuery->count() . ') ',
        'content' => $location,
    ]
];

echo '<div class="tab-wrapper">';

echo Tabs::widget([
    'items' => $items
]);

$nearestPage = $contentTreeItem->getPage();
$updateItems = $model->getUpdateTranslationItems();
$newTranslationModel = new \yii\base\DynamicModel(['tableName', 'id', 'from', 'to']);
$newTranslationModel->addRule(['from', 'tableName', 'to', 'from'], 'string', ['max' => 55]);

?>
    <div id="action-button-content">
        <?php if ($model->getNotTranslatedLanguages()):
            Modal::begin([
                'id' => 'add-new-translation',
                'header' => '<h2>Add New Translation</h2>',
                'bodyOptions' => ['class' => 'modal-body', 'id' => 'add-new-translation-modal-body'],
                'size' => 'modal-lg',
                'toggleButton' => ['label' => 'Add New Translation ', 'class' => 'btn btn-success margin-5'],
            ]);
            ?>
            <?php $form = \yii\widgets\ActiveForm::begin(['action' => ['/base/add-new-language']]) ?>
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">New Translation</h3>
                    <?php echo $form->field($newTranslationModel, 'to')->dropDownList($model->getNotTranslatedLanguages())->label(false) ?>
                </div>
            </div>
            <div class="card" style="margin-top: 10px">
                <div class="card-body">
                    <h3 class="card-title">Translation Based On</h3>
                    <?php echo $form->field($newTranslationModel, 'from')->dropDownList(array_merge([0 => 'None'], $model->getTranslatedLanguages()))->label(false) ?>
                </div>
            </div>
            <?php echo $form->field($newTranslationModel, 'tableName')->hiddenInput(['value' => $model->getFormattedTableName()])->label(false) ?>
            <?php echo $form->field($newTranslationModel, 'id')->hiddenInput(['value' => $model->id])->label(false) ?>
            <?php echo Html::submitButton(Yii::t('intermundiacms', 'Add'), ['class' => 'btn btn-primary']) ?>
            <?php yii\widgets\ActiveForm::end(); ?>
            <?php Modal::end();endif; ?>
        <?php echo ButtonDropdown::widget([
            'label' => 'Update',
            'options' => ['class' => 'btn btn-primary'],
            'dropdown' => [
                'items' => $updateItems,
                'options' => [
                    'class' => 'dropdown-menu-left',
                ],
            ],]);
        ?>
        <?php echo Html::a('View', $contentTreeItem->getFrontendUrl(), ['class' => 'btn btn-warning  margin-5', 'target' => '_blank']); ?>


        <?php if ($contentTreeItem->table_name != 'website'): ?>
            <?php echo Html::a(Yii::t('intermundiacms', 'delete'), $model->getDeleteUrl($contentTreeItem->id), [
                'title' => Yii::t('intermundiacms', 'delete'),
                [' class' => 'btn btn-danger  margin-5'],
                'data-confirm' => Yii::t('yii', 'Are you sure you want to add new translation'),
                'data-method' => 'post',
            ]); ?>
        <?php endif; ?>
        <?php if ($nearestPage) {
            echo Html::a('Live Content Editing', Url::to([
                '/base/user-login',
                'id' => Yii::$app->user->id,
                'contentTreeId' => $nearestPage->id
            ]),
                ['class' => 'btn btn-info margin-5', 'target' => '_blank']);
        } ?>
        <hr>
    </div>
    <div class="well well-sm">
        <?php

        echo ButtonDropdown::widget([
            'encodeLabel' => false,
            'options' => ['class' => 'btn btn-default'],
            'label' => '<i class="fa fa-plus"></i> ' . Yii::t('intermundiacms', 'Create Content'),
            'dropdown' => [
                'items' => array_map(function ($item) use ($contentTreeItem) {
                    return [
                        'label' => $item['displayName'],
                        'url' => ['base/create', 'contentType' => $item['contentType'], 'parentContentId' => $contentTreeItem->id, 'language' => Yii::$app->language],
                    ];
                }, Yii::$app->contentTree->getEditableClasses())
            ],
        ]); ?>
        <?php if ($contentTreeItem->id != 1): ?>
            <?php
            echo Html::button('Choose From Existing', ['style' => 'margin-left:10px;margin-right:10px; ', 'class' => 'btn btn-default', 'data-toggle' => 'modal', 'data-target' => '#linked', 'data-key' => $contentTreeItem->id]);
            Modal::begin([
                'id' => 'linked',
                'header' => '<h2>Choose From Existing Trees</h2>',
                'bodyOptions' => ['class' => 'modal-body', 'id' => 'tree-modal-body', 'data-key' => $contentTreeItem->id],
                'size' => 'modal-lg',
                'footer' => Html::button('Link', ['class' => 'btn btn-primary', 'id' => 'linked-button']),
            ]);

            ?>

            <form id="table_names_tree" class="form-inline"
                  style="display: inline-block">
                <?php echo Html::checkboxList('table_names', [], $tableNames); ?>
            </form>

            <div id="jstree-choose"></div>


            <?php
            Modal::end();
            echo Html::button('Move To', ['style' => 'margin-left:10px;margin-right:10px; ', 'class' => 'btn btn-default', 'data-toggle' => 'modal', 'data-target' => '#move-modal', 'data-key' => $contentTreeItem->id]); ?>
        <?php endif; ?>
        <?php
        Modal::begin([
            'id' => 'move-modal',
            'header' => '<h2>Move From Tree To Tree</h2>',
            'bodyOptions' => ['class' => 'modal-body', 'id' => 'move-modal-body', 'data-key' => $contentTreeItem->id],
            'size' => 'modal-lg',
            'footer' => Html::button('Move', ['class' => 'btn btn-primary', 'id' => 'move-button']),
        ]);
        ?>
        <form id="table_names_for_move" class="form-inline"
              style="display: inline-block">
            <?php echo Html::checkboxList('table_names_move_id', [], $tableNames); ?>
        </form>

        <div id="jstree-move"></div>


        <?php
        Modal::end();

        ?>


    </div>
<?php

echo $this->render('children', [
    'query' => $contentTreeItem->getDirectChildren()->notDeleted(),
    'contentTreeItem' => $contentTreeItem
]);
