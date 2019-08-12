<?php

namespace intermundia\yiicms\modules\file\controllers;

use alexantr\elfinder\CKEditorAction;
use alexantr\elfinder\ConnectorAction;
use intermundia\yiicms\web\BackendController;
use Yii;

class ManagerController extends BackendController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'connector' => [
                'class' => ConnectorAction::class,
                'options' => [
                    'disabledCommands' => ['netmount'],
                    'connectOptions' => [
                        'filter'
                    ],
                    'roots' => [
                        [
                            'driver' => 'LocalFileSystem',
                            'path' => Yii::getAlias('@storage/web/source'),
                            'URL' => Yii::getAlias('@storageUrl/source'),
                            'uploadDeny' => [
                                'text/x-php', 'text/php', 'application/x-php', 'application/php'
                            ],
                        ],
                    ],
                ],
            ],
            'ckeditor' => [
                'class' => CKEditorAction::class,
                'connectorRoute' => 'connector',
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
