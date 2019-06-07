<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model intermundia\yiicms\models\Language */

$this->title = Yii::t('intermundiacms', 'Create {modelClass}', [
    'modelClass' => 'Language',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('intermundiacms', 'Languages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="language-create">

    <?php echo $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
