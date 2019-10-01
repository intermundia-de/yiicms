<?php
/**
 * User: zura
 * Date: 6/25/18
 * Time: 11:35 AM
 */

namespace intermundia\yiicms\web;

use intermundia\yiicms\helpers\LanguageHelper;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;

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