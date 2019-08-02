<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 8/2/14
 * Time: 11:20 AM
 */

namespace intermundia\yiicms\controllers;

use intermundia\yiicms\models\User;
use DateTime;
use intermundia\yiicms\web\BackendController;
use intermundia\yiicms\models\AccountForm;
use intermundia\yiicms\models\LoginForm;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class SignInController extends BackendController
{

    public $defaultAction = 'unlock';

    //Time in seconds

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post']
                ]
            ]
        ];
    }

    public function actions()
    {
        return [
            'avatar-upload' => [
                'class' => UploadAction::class,
                'deleteRoute' => 'avatar-delete',
                'on afterSave' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file;
                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
                    $file->put($img->encode());
                }
            ],
            'avatar-delete' => [
                'class' => DeleteAction::class
            ]
        ];
    }


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
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            $user = $model->getUser();
            if (!$user) {
                $model->addError('username', 'Such user does not exist!');
                return $this->render('login', [
                    'model' => $model
                ]);
            } else {
                if ($model->isSuspended()) {
                    if ($user->suspended_till <= time()) {
                        if (!$user->activate()) {
                            return $this->render('login', [
                                'model' => $model
                            ]);
                        }
                        if ($model->login()) {
                            return $this->goBack();
                        }
                    } else {
                        Yii::$app->session->setFlash('error', "Your User is suspended for: {$user->getSuspendTime()}");
                        return $this->goHome();
                    }
                } else if (!$model->isSuspended()) {
                    if ($model->login()) {
                        if (!$user->activate()) {
                            return $this->render('login', [
                                'model' => $model
                            ]);
                        }
                        return $this->goBack();
                    } else {
                        if (!$user->increaseLoginAttempt()) {
                            return $this->render('login', [
                                'model' => $model
                            ]);
                        }
                        if ($user->login_attempt === Yii::$app->user->loginAttemptCount) {
                            if (!$user->suspend()) {
                                return $this->render('login', [
                                    'model' => $model
                                ]);
                            }else{
                                Yii::$app->session->setFlash('error', "Your User is suspended for: {$user->getSuspendTime()}");
                            }
                            return $this->goHome();
                        }
                        if ($user->login_attempt < Yii::$app->user->loginAttemptCount) {
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

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /**
     *
     *
     * @return string|\yii\web\Response
     * @throws \yii\web\ForbiddenHttpException
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function actionUnlock()
    {
        $this->layout = 'base';
        $lockedUser = Yii::$app->session->get('lockedUser');
        $lockedUserUsername = ArrayHelper::getValue($lockedUser, 'username');
        if (!$lockedUserUsername) {
            return $this->redirect(['login']);
        }

        $model = new LoginForm();
        $model->username = $lockedUserUsername;
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('unlock', [
                'lockedUser' => $lockedUser,
                'model' => $model
            ]);
        }

    }

    public function actionLock()
    {
        $user = Yii::$app->user->identity;
        Yii::$app->user->logout();
        Yii::$app->session->set('lockedUser', [
            'username' => $user->username,
            'email' => $user->email,
            'fullname' => $user->userProfile->getFullName(),
            'avatar' => $user->userProfile->getAvatar('/img/anonymous.jpg')
        ]);
        return $this->redirect(['unlock']);
    }

    public function actionProfile()
    {
        $model = Yii::$app->user->identity->userProfile;
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => Yii::t('backend', 'Your profile has been successfully saved', [], $model->locale)
            ]);
            return $this->refresh();
        }
        return $this->render('profile', ['model' => $model]);
    }

    /**
     *
     *
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function actionAccount()
    {
        $user = Yii::$app->user->identity;
        $model = new AccountForm();
        $model->username = $user->username;
        $model->email = $user->email;
        if ($model->load($_POST) && $model->validate()) {
            $user->username = $model->username;
            $user->email = $model->email;
            if ($model->password) {
                $user->setPassword($model->password);
            }
            $user->save();
            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => Yii::t('backend', 'Your account has been successfully saved')
            ]);
            return $this->refresh();
        }
        return $this->render('account', ['model' => $model]);
    }
}
