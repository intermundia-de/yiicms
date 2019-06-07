<?php

use backend\widgets\LanguageSelector;

/**
 * @var $this  yii\web\View
 * @var $model \intermundia\yiicms\models\CarouselTranslation
 * @var $contentTreeModel \intermundia\yiicms\models\ContentTree
 */
?>


<?php echo $form->field($model, 'language')->widget(LanguageSelector::class, []) ?>
<?php echo $this->render('../content-tree/_model_fields', [
    'contentTreeModel' => $contentTreeModel,
    'form' => $form,
]); ?>
<?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
