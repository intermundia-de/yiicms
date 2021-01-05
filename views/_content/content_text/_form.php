<?php

use intermundia\yiicms\widgets\FileInput;
use backend\widgets\LanguageSelector;
use yii\helpers\Html;

/**
 * @var $this  yii\web\View
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */
?>

<?php echo $form->field($model, 'language')->widget(LanguageSelector::class, []) ?>

<?php echo $this->render('../content-tree/_model_fields', [
    'contentTreeModel' => $contentTreeModel,
    'form' => $form,
]); ?>

<?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
<?php echo $form->field($model, 'single_line')->textInput(['maxlength' => true]) ?>

<?php echo $form->field($model, 'multi_line')->widget(\intermundia\yiicms\widgets\CKEditor::class, [
    'preset' => 'full'
]) ?>

<?php echo $form->field($model, 'multi_line2')->widget(\intermundia\yiicms\widgets\CKEditor::class, [
    'preset' => 'full'
]) ?>

<?php
echo $form->field($model, 'image[]')->widget(FileInput::class, [
    'options' => ['accept' => 'image/*', 'multiple' => true],
]); ?>
