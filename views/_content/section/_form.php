<?php

use backend\widgets\LanguageSelector;

/**
 * @var $this  yii\web\View
 * @var $model intermundia\yiicms\models\CountryTranslation
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */
?>

<?php echo $form->field($model, 'language')->widget(LanguageSelector::class, []) ?>
<?php echo $this->render('../content-tree/_model_fields', [
    'contentTreeModel' => $contentTreeModel,
    'form' => $form,
]); ?>
<?php echo $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

<?php echo $form->field($model, 'template')->widget(\intermundia\yiicms\widgets\CKEditor::class,[
    'preset' => 'full',
    'clientOptions' => [
        'startupMode' => 'source'
    ]
]) ?>

<?php echo $form->field($model, 'description')->widget(\intermundia\yiicms\widgets\CKEditor::class,[
    'preset' => 'full'
]) ?>
