<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel intermundia\yiicms\models\search\Language */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('intermundiacms', 'Languages');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="language-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php echo Html::a(Yii::t('intermundiacms', 'Create {modelClass}', [
    'modelClass' => 'Language',
]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'code',
            'name',
            'created_at:datetime',
            'updated_at:datetime',
            // 'updated_by',
            // 'deleted_at',
            // 'deleted_by',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
