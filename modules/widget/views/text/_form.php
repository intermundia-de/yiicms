<?php

use intermundia\yiicms\widgets\CKEditor;
use intermundia\yiicms\widgets\LanguageSelector;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use intermundia\yiicms\bundle\JSTreeAsset;

/**
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\WidgetText
 * @var $modelTranslation intermundia\yiicms\models\WidgetTextTranslation
 */


$this->registerAssetBundle(JSTreeAsset::class);

?>
<div class="widget-text-form">

    <?php $form = ActiveForm::begin([
        'enableClientValidation' => false,
        'enableAjaxValidation' => true,
    ]) ?>

    <?php echo $form->field($model, 'key')->textInput(['maxlength' => 1024]) ?>

    <?php echo $form->field($model, 'status')->checkbox() ?>

    <div class="well well-sm">

        <?php echo $form->field($modelTranslation, 'language')->widget(LanguageSelector::class, []) ?>

        <?php echo $form->field($modelTranslation, 'title')->textInput(['maxlength' => 512]) ?>

        <?php echo $form->field($modelTranslation, 'body')->widget(CKEditor::class, [
            'options' => ['rows' => 10],
            'clientOptions' => [
                'autoParagraph' => false
            ],
            'preset' => 'full'
        ]) ?>

        <?php echo $form->field($modelTranslation, 'short_description')->widget(CKEditor::class, [
            'options' => ['rows' => 10],
            'preset' => 'full'
        ]) ?>

    </div>

    <div class="form-group">
        <?php echo Html::submitButton(
            $model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']
        ) ?>
    </div>

    <?php ActiveForm::end() ?>
</div>

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
