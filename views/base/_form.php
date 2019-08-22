<?php

/** @var $model \intermundia\yiicms\models\BaseTranslateModel */

use intermundia\yiicms\widgets\LanguageSelector;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/** @var $contentTreeModel \intermundia\yiicms\models\ContentTree */
/** @var $url string */
/** @var $tableName string */
/** @var $contentType string */

?>
<?php $form = ActiveForm::begin([
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data'
    ]
//    'enableAjaxValidation' => true,
]) ?>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<div class="row">
    <div class="col-lg-9 col-md-8">
        <div class="panel panel-default lobipanel">
            <div class="panel-heading">
                <h4 class="panel-title"><?php echo Yii::t('intermundiacms', 'Content Fields') ?></h4>
            </div>
            <div class="panel-body">
                <?php echo $this->render('../_content/' . $contentType . '/_form', [
                    'model' => $model,
                    'tableName' => $tableName,
                    'contentType' => $contentType,
                    'contentTreeModel' => $contentTreeModel,
                    'form' => $form
                ]); ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"> <?php echo Yii::t('intermundiacms', 'Base fields') ?></h3>
            </div>
            <div class="panel-body">
                <?php echo $form->field($model, 'language')->widget(LanguageSelector::class, []) ?>
                <?php echo $this->render('../content-tree/_model_fields', [
                    'contentTreeModel' => $contentTreeModel,
                    'form' => $form,
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $this->render('buttons', ['model' => $model, 'url' => $url]); ?>
<?php echo Html::hiddenInput('go_to_parent', '0', ['id' => 'idGoToParent']); ?>
<?php ActiveForm::end() ?>
