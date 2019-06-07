<?php
/**
 * User: zura
 * Date: 6/25/18
 * Time: 11:35 AM
 */

namespace intermundia\yiicms\web;

use intermundia\yiicms\helpers\LanguageHelper;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\Website;

/**
 * Class Application
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class BackendApplication extends BaseApplication
{
    public $defaultAlias = null;

    public $defaultRoute = 'content-tree/index';

    /**
     * Application before request method
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeRequest()
    {
        parent::beforeRequest();
    }



    /**
     * Return current `nodes` param from $_GET data. Return `$defaultAlias` if `nodes` is empty
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return string
     */
    public function getCurrentAlias()
    {
        return \Yii::$app->request->get('nodes') ?: $this->defaultAlias;
    }
}