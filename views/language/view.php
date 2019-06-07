<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model intermundia\yiicms\models\Language */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('intermundiacms', 'Languages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="language-view">

    <p>
        <?php echo Html::a(Yii::t('intermundiacms', 'Update'), ['update', 'id' => $model->code], ['class' => 'btn btn-primary']) ?>
        <?php echo Html::a(Yii::t('intermundiacms', 'Delete'), ['delete', 'id' => $model->code], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('intermundiacms', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?php echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'code',
            'name',
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
