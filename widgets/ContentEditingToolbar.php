<?php
/**
 * User: zura
 * Date: 10/17/18
 * Time: 3:04 PM
 */

namespace intermundia\yiicms\widgets;


use frontend\modules\user\models\LoginForm;
use intermundia\yiicms\models\ContentTree;
use Yii;
use yii\bootstrap\Alert;
use yii\bootstrap\Widget;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;

/**
 * Class ContentEditingHeader
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\widgets
 */
class ContentEditingToolbar extends Widget
{
    public $showLogin = false;

    public function run()
    {
        return $this->render('editing_toolbar.php', [
            'widget' => $this,
        ]);

        if (\Yii::$app->user->canEditContent()) {

            /** @var ContentTree $contentTreeObject */
            $contentTreeObject = Yii::$app->pageContentTree;

            return Alert::widget([
                'closeButton' => false,
                'options' => [
                    'class' => 'alert alert-warning content-editor'
                ],
                'body' => '<div class="container">
                    <div class="row">
                        <div class="col-md-7">
                            <span>' . Yii::t('frontend', 'You are in the Content Editing mode') . '</span>
                        </div>
                        <div class="col-md-5 text-right">
                            <label>
                                <input id="with-hidden-checkbox" type="checkbox" name="with-hidden" ' .
                    ( !Yii::$app->request->get('hidden') ? '' : 'checked' ) . ' > ' .
                    Yii::t('frontend', 'With Hidden') .
                    '</label>' .
                    \yii\helpers\Html::a(Yii::t('frontend', 'Edit in backend'), $contentTreeObject->getBackendFullUrl()
                        ,
                        [
                            'id' => 'to-backend-url',
//                                        'data-backend-url' => Yii::getAlias('@backendUrl/content/website') . Yii::$app->request->url,
//                                        'data-method' => 'post',
                            'class' => 'btn btn-sm btn-warning btn-content-editing',
                            'target' => '_blank'
                        ]) .
                    \yii\helpers\Html::a(Yii::t('frontend', 'Logout'),
                        \yii\helpers\Url::to(['/user/sign-in/logout']),
                        ['data-method' => 'post', 'class' => 'btn btn-sm btn-danger btn-content-editing btn-logout']) . '
                        </div>
                    </div>
                </div>'
            ]);
        } else if ($this->showLogin) {

        }
    }

    public function beginForm()
    {

        $form = ActiveForm::begin([
            'id' => 'login-form',
            'layout' => 'inline',
            'action' => ['/user/sign-in/login']
        ]);
        $content = $form->field($model, 'identity');
        $content .= $form->field($model, 'password')->passwordInput();
        $content .= '<div class="form-group">' .
            Html::submitButton(Yii::t('frontend', 'Login'), ['class' => 'btn btn-primary', 'name' => 'login-button']) .
            '</div>';

        return $content;
    }
}