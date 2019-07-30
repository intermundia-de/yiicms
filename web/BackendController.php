<?php
/**
 * User: zura
 * Date: 9/21/18
 * Time: 2:14 PM
 */

namespace intermundia\yiicms\web;


use backend\models\LoginForm;
use common\models\User;
use Yii;
use DateTime;

/**
 * Class BackendController
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class BackendController extends Controller
{

    /**
     *
     *
     * @return string|\yii\web\Response
     * @throws \yii\web\ForbiddenHttpException
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function actionLogin()
    {
        $this->layout = 'base';
        $this->getView()->params['body-style'] = "background-image: url('/img/login-bg.jpg')";
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();


        if ($model->load(Yii::$app->request->post())) {
            $user = $model->getUser();
            if (!$user) {
                $model->addError('password', 'Such user does not exist!');
                return $this->render('login', [
                    'model' => $model
                ]);
            } else {
                if ($model->isSuspended()) {
                    if ($user->suspended_till <= time()) {
                        $user->status = User::STATUS_ACTIVE;
                        $user->login_attempt = 0;
                        $user->suspended_till = 0;
                        if (!$user->save()) {
                            Yii::$app->session->setFlash('error', "Something went wrong;1111");
                        }
                        if ($model->login()) {
                            return $this->goBack();
                        }
                    } else {
                        $currentTime = new DateTime('@' . (string)time());
                        $suspendedTill = new DateTime('@' . (string)$user->suspended_till);
                        $interval = $currentTime->diff($suspendedTill);
                        Yii::$app->session->setFlash('error', "Your User is suspended for: {$interval->format('%Hh %Im %Ss')}");
                        return $this->goHome();
                    }
                } else if (!$model->isSuspended()) {
                    if ($model->login()) {
                        $user->status = User::STATUS_ACTIVE;
                        $user->login_attempt = 0;
                        $user->suspended_till = 0;
                        if (!$user->save()) {
                            Yii::$app->session->setFlash('error', "Something went wrong;1111");
                        }
                        return $this->goBack();
                    } else {
                        $user->login_attempt++;
//                        TODO ADD LOGIN ATTEMPT CONST
                        if ($user->login_attempt === 3) {
                            $user->status = User::STATUS_SUSPENDED;
//                            TODO ADD SUSPEND TIME CONST
                            $user->suspended_till = time() + 60;
                            $currentTime = new DateTime('@' . (string)time());
                            $suspendedTill = new DateTime('@' . (string)$user->suspended_till);
                            $interval = $currentTime->diff($suspendedTill);
                            Yii::$app->session->setFlash('error', "Your User is suspended for: {$interval->format('%Hh %Im %Ss')}");
                        }
                        if (!$user->save()) {
                            Yii::$app->session->setFlash('error', "Something went wrong; 2222");
                        }
                        if ($user->login_attempt < 3) {
                            return $this->render('login', [
                                'model' => $model
                            ]);
                        }
                        return $this->goHome();
                    }
                }
            }

        }
        return $this->render('login', [
            'model' => $model
        ]);

    }
}
