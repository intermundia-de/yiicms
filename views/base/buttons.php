<?php
/**
 * Created by PhpStorm.
 * User: guga
 * Date: 6/26/18
 * Time: 12:08 PM
 */

use yii\helpers\Html;

?>


<div class="form-group">
    <?php echo Html::submitButton(Yii::t('intermundiacms', $model->isNewRecord ? 'Create' : 'Update'), [
        'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
    ]) ?>
    <?php echo Html::submitButton(Yii::t('intermundiacms',
        $model->isNewRecord ? 'Create and go to parent' : 'Update and go to parent'),
        [
            'name' => 'go_to_parent',
            'value' => '1',
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
        ]) ?>
    <?php echo Html::submitButton(Yii::t('intermundiacms',
        $model->isNewRecord ? 'Create and stay' : 'Update and stay'),
        [
            'name' => 'stay_here',
            'value' => '1',
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
        ]) ?>
    <?php echo Html::a(Yii::t('intermundiacms', 'Cancel'), [$url], ['class' => 'btn btn-danger']) ?>
</div>