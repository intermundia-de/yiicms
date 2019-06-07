<?php

/**
 * @var $contentTreeModel intermundia\yiicms\models\ContentTree
 */
?>

<?php echo $form->field($contentTreeModel, 'key')->textInput(['maxlength' => true]) ?>
<?php
echo $form->field($contentTreeModel, 'show_as_sibling')
    ->dropDownList(['0' => Yii::t('backend', 'Show'), '1' => Yii::t('backend', 'Hide')])
?>
<?php echo $form->field($contentTreeModel, 'custom_class')->widget(kartik\select2\Select2::class, [
    'data' => $contentTreeModel->getCustomCssClassList(),
    'changeOnReset' => false,
    'theme' => kartik\select2\Select2::THEME_CLASSIC,
    'options' => ['multiple' => true, 'placeholder' => 'Select custom Classes...', 'value' => explode(',', $contentTreeModel->custom_class)],
    'pluginOptions' => [
        'allowClear' => true
    ],
]) ?>