<?php
/**
 * User: zura
 * Date: 10/17/18
 * Time: 3:04 PM
 */

namespace intermundia\yiicms\widgets;


use intermundia\yiicms\models\ContentTree;
use Yii;
use yii\bootstrap\Alert;
use yii\bootstrap\Widget;

/**
 * Class ContentEditingHeader
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\widgets
 */
class ContentEditingToolbar extends Widget
{
    public function run()
    {
        if (\Yii::$app->user->canEditContent()) {

            /** @var ContentTree $contentTreeObject */
            $contentTreeObject = $this->getView()->contentTreeObject;
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
                                (!Yii::$app->request->get('hidden') ? '' : 'checked') . ' > ' .
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
                                ]).
                            \yii\helpers\Html::a(Yii::t('frontend', 'Logout'),
                                \yii\helpers\Url::to(['/user/sign-in/logout']),
                                ['data-method' => 'post', 'class' => 'btn btn-sm btn-danger btn-content-editing btn-logout']) . '
                        </div>
                    </div>
                </div>'
            ]);
        }
    }
}