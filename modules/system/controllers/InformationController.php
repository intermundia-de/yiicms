<?php

namespace intermundia\yiicms\modules\system\controllers;

use intermundia\yiicms\web\BackendController;
use Probe\ProviderFactory;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class InformationController extends BackendController
{

    public function actionIndex()
    {
        $provider = ProviderFactory::create();
        if ($provider) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                if ($key = Yii::$app->request->get('data')) {
                    switch ($key) {
                        case 'cpu_usage':
                            return $provider->getCpuUsage();
                            break;
                        case 'memory_usage':
                            return ($provider->getTotalMem() - $provider->getFreeMem()) / $provider->getTotalMem();
                            break;
                    }
                }
            } else {
                return $this->render('index', ['provider' => $provider]);
            }
        }

        return $this->render('fail');
    }
}
