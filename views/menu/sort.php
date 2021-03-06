<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 11/5/18
 * Time: 7:00 PM
 */

use yii\helpers\Html;

/** @var $query  \intermundia\yiicms\models\query\ContentTreeMenuQuery */
/** @var $model \intermundia\yiicms\models\Menu */
/** @var $this \intermundia\yiicms\web\View */

?>

<p>
    <?php echo Html::tag('h1', Yii::t('backend', '{MenuItem}', [
        'MenuItem' => $model->name,
    ])) ?>
</p>
<?php
echo \yii\grid\GridView::widget([
    'dataProvider' => new \yii\data\ActiveDataProvider([
        'query' => $query,
        'pagination' => false
    ]),
    'tableOptions' => ['class' => 'table table-striped table-bordered', 'id' => 'menu_tree_item'],
    'columns' => [
        'id',
        [
            'label' => '',
            'content' => function () {
                return '<div style="cursor: pointer" class="pointer glyphicon glyphicon-align-justify"></div>';
            },
            'contentOptions' => ['class' => 'tree-children-draggable']
        ],
        [
            'label' => Yii::t('intermundiacms', 'Content Tree Item'),
            'content' => function ($model) {
                /** @var $model \intermundia\yiicms\models\ContentTreeMenu */
                return $model->contentTree->getName();
            },
            'contentOptions' => ['class' => 'not-draggable'],
        ]
    ]
]);
?>

<?php
$this->registerAssetBundle(\intermundia\yiicms\bundle\MenuTreeAsset::class);
?>
