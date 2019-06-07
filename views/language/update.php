<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model intermundia\yiicms\models\Language */

$this->title = Yii::t('intermundiacms', 'Update {modelClass}: ', [
    'modelClass' => 'Language',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('intermundiacms', 'Languages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->code]];
$this->params['breadcrumbs'][] = Yii::t('intermundiacms', 'Update');
?>
<div class="language-update">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
