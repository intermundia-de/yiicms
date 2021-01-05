<?php

use intermundia\yiicms\widgets\FileInput;
use backend\widgets\LanguageSelector;

/**
 * @var $this  yii\web\View
 * @var $model \intermundia\yiicms\models\CarouselItemTranslation
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */
?>


<?php echo $form->field($model, 'language')->widget(LanguageSelector::class, []) ?>
<?php echo $this->render('../content-tree/_model_fields', [
    'contentTreeModel' => $contentTreeModel,
    'form' => $form,
]); ?>
<?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

<?php echo $form->field($model, 'caption')->widget(\intermundia\yiicms\widgets\CKEditor::class, [
    'preset' => 'full'
]) ?>

<?php
echo $form->field($model, 'image[]')->widget(FileInput::class, [
    'options' => ['accept' => 'image/*', 'multiple' => true],
]); ?>
