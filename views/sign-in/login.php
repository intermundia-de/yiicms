<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \backend\models\LoginForm */

$bundle = \intermundia\yiicms\bundle\BackendLoginAsset::register($this);
$bgImage = $bundle->baseUrl . '/bg.jpg';
$this->registerCss("
body{
    background: url('$bgImage') no-repeat center;
    background-size: cover;
}
");
$this->title = Yii::t('backend', 'Sign In');
$this->params['breadcrumbs'][] = $this->title;
$this->params['body-class'] = 'login-page';
?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
<?php endif; ?>
<div class="login-wrapper fadeInDown animated">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => [
            'class' => 'lobi-form login-form visible'
        ],
        'fieldConfig' => function ($model, $attribute) {
            $icon = ' <span class="input-icon input-icon-prepend fa fa-user"></span>';
            $tooltip = '<span class="tooltip tooltip-top-left"><i class="fa fa-user text-cyan-dark"></i> '
                . Yii::t('cmsCore', 'Please enter the username')
                . '</span>';
            if ($attribute === 'password') {
                $icon = '<span class="input-icon input-icon-prepend fa fa-key"></span>';
                $tooltip = '<span class="tooltip tooltip-top-left"><i class="fa fa-key text-cyan-dark"></i> '
                    . Yii::t('cmsCore', 'Please enter your password')
                    . '</span>';
            }

            return [
                'template' => "{label}<label class=\"input\">$icon{input}$tooltip</label>{error}"
            ];
        }
    ]); ?>
    <div class="login-header">
        <?php echo Yii::t('cmsCore', 'Login to your account') ?>
    </div>
    <div class="login-body no-padding">
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div style="position: relative ;width: 100%; z-index:2" class="alert alert-error btn-danger">
                <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
        <fieldset>
            <?php echo $form->field($model, 'username') ?>
            <?php echo $form->field($model, 'password')->passwordInput() ?>
            <div class="row">
                <div class="col-xs-8">
                    <label class="checkbox lobicheck lobicheck-info lobicheck-inversed lobicheck-lg">
                        <input type="checkbox" id="loginform-rememberme" class="simple" name="LoginForm[rememberMe]"
                               value="1">
                        <?php echo $model->attributeLabels()['rememberMe'] ?>
                        <i></i>
                    </label>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-info btn-block"><span
                                class="glyphicon glyphicon-log-in"></span> Login
                    </button>
                </div>
            </div>
        </fieldset>
    </div>
    <?php ActiveForm::end() ?>
</div>
