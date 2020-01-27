<?php
/**
 * User: zura
 * Date: 6/25/18
 * Time: 11:35 AM
 */

namespace intermundia\yiicms\web;

use intermundia\yiicms\helpers\LanguageHelper;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\WebsiteTranslation;

/**
 * Class Application
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class Application extends BaseApplication
{
    public $defaultAlias = null;

    public $defaultRoute = 'content-tree/index';

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var ContentTree[]
     */
    public $contentTreeObjects = [];

    /**
     * Application before request method
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeRequest()
    {
        parent::beforeRequest();

        $website = \Yii::$app->websiteContentTree;
        if ($website) {
            $websiteTranslation = $website->getModel()->activeTranslation;
            if ($websiteTranslation->usersnap_code && $websiteTranslation->usersnap_type !== WebsiteTranslation::USERSNAP_TYPE_DISABLED) {
                $registerUsersnap = false;
                if ($websiteTranslation->usersnap_type === WebsiteTranslation::USERSNAP_TYPE_GET_PARAM) {
                    if (\Yii::$app->request->get('usersnap')) {
                        \Yii::$app->session->set('usersnap', true);
                    }
                    if (\Yii::$app->session->get('usersnap')) {
                        $registerUsersnap = true;
                    }
                } elseif ($websiteTranslation->usersnap_type === WebsiteTranslation::USERSNAP_TYPE_ALWAYS) {
                    $registerUsersnap = true;
                }

                if ($registerUsersnap) {
                    \Yii::$app->view->registerJs("
                        (function() { var s = document.createElement(\"script\"); s.type = \"text/javascript\"; 
                        s.async = true; s.src = '//api.usersnap.com/load/{$websiteTranslation->usersnap_code}.js'; 
                        var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })(); 
                    ");
                }
            }
        }
//
        if ($this->hasLanguageInUrl) {
            $rules = &$this->urlManager->rules;
            list($removed) = array_splice($rules, count($rules) - 1, 1);

            $langCode = LanguageHelper::convertLongCodeIntoShort($this->language);
            $this->urlManager->addRules([
                [
                    'pattern' => '<lang:' . $langCode . '>',
                    'route' => 'content-tree/index',
                    'encodeParams' => false,
                ],
                [
                    'pattern' => "$langCode/<nodes:.*>",
                    'route' => 'content-tree/index',
                    'encodeParams' => false,
                ],
                $removed
            ]);
        }
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