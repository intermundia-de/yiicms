<?php
/**
 * User: zura
 * Date: 7/23/19
 * Time: 1:36 PM
 */

use frontend\modules\user\models\LoginForm;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Alert;
use yii\helpers\Html;

/** @var $widget \intermundia\yiicms\widgets\ContentEditingToolbar */
/** @var $this \yii\web\View */
$model = new LoginForm();
?>


<?php if (Yii::$app->user->canEditContent()): ?>
    <?php Alert::begin([
        'closeButton' => false,
        'options' => [
            'class' => 'alert alert-warning content-editor'
        ]
    ]); ?>
    <div class="container">
        <div class="row">

            <div class="col-md-7">
                <span>
                    <?php echo Yii::t('frontend', 'You are in the Content Editing mode') ?>
                </span>
            </div>
            <div class="col-md-5 text-right">
                <label>
                    <input id="with-hidden-checkbox" type="checkbox" name="with-hidden"
                        <?php echo( !Yii::$app->request->get('hidden') ? '' : 'checked' ) ?>>
                    <?php echo Yii::t('frontend', 'With Hidden') ?>
                </label> <?php echo \yii\helpers\Html::a(
                        Yii::t('frontend', 'Edit in backend'), Yii::$app->pageContentTree->getBackendFullUrl(),
                        [
                            'id' => 'to-backend-url',
                            'class' => 'btn btn-sm btn-warning btn-content-editing',
                            'target' => '_blank'
                        ]) . '&nbsp' .
                    \yii\helpers\Html::a(Yii::t('frontend', 'Logout'),
                        \yii\helpers\Url::to(['/user/sign-in/logout']),
                        ['data-method' => 'post', 'class' => 'btn btn-sm btn-danger btn-content-editing btn-logout'])
                ?>
            </div>
        </div>
    </div>
    <?php Alert::end() ?>

<?php elseif ($widget->showLogin): ?>
    <?php Alert::begin([
        'closeButton' => false,
        'options' => [
            'class' => 'alert alert-warning content-editor'
        ]
    ]); ?>
    <div class="container">
        <div class="row">

            <div class="col-md-7">
                <span>
                    <?php echo Yii::t('frontend', 'Enter Your username and password to enable frontend editing') ?>
                </span>
            </div>
            <div class="col-md-5 text-right">
                <?php $form = ActiveForm::begin([
                    'id' => 'login-form',
                    'layout' => 'inline',
                    'action' => ['/user/sign-in/login']
                ]); ?>

                <?php echo $form->field($model, 'identity', [
                    'inputOptions' => [
                        'placeholder' => $model->attributeLabels()['identity']
                    ]
                ]) ?>
                <?php echo $form->field($model, 'password', [
                    'inputOptions' => [
                        'placeholder' => $model->attributeLabels()['password']
                    ]
                ])->passwordInput() ?>
                <div class="form-group">
                    <?php echo Html::submitButton(Yii::t('frontend', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
                <?php ActiveForm::end() ?>
            </div>
        </div>
    </div>
    <?php Alert::end() ?>

<?php endif; ?>