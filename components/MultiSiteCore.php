<?php
/**
 * User: zura
 * Date: 2/25/19
 * Time: 6:00 PM
 */

namespace intermundia\yiicms\components;


use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class MultisiteCore
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\components
 */
class MultiSiteCore extends Component
{
    /**
     * The template of websites is like this
     *
     * 'websites' => [
     *   'website key' => [
     *       'defaultContentId' => content tree id,
     *       'masterLanguage' => 'en-US',
     *       'environments' => [
     *           "local" => [ // Environment key
     *               "storageUrl" => 'storage url',
     *               "domains" => [
     *                   'domain1' => 'en-US',
     *                   'domain2' => 'en-US',
     *               ]
     *           ],
     *           "testing" => [
     *               "storageUrl" => 'storage url',
     *               "domains" => [
     *                   'domain1' => 'en-US',
     *                   'domain2' => 'en-US',
     *               ]
     *           ]
     *       ]
     *   ],
     *   'website key' => [
     *       'defaultContentId' => content tree id,
     *       'masterLanguage' => 'en-US',
     *       'environments' => [
     *           "local" => [
     *               "storageUrl" => 'storage url',
     *               "domains" => [
     *                   'domain1' => 'en-US',
     *                   'domain2' => 'en-US',
     *               ]
     *           ],
     *           "apollo" => [
     *               "storageUrl" => 'storage url',
     *               "domains" => [
     *                   'domain1' => 'en-US',
     *                   'domain2' => 'en-US',
     *               ]
     *           ]
     *       ]
     *   ]
     * ]
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var array
     */
    public $websites = [];

    public function init()
    {
        parent::init();
        if (empty($this->websites)){
            throw new InvalidConfigException(self::class.'::$domainLanguageMapping can not be empty');
        }

//        $currentDomain = Yii::$app->request->hostName;
//        Yii::$app->language = ArrayHelper::getValue($this->websites, $currentDomain, Yii::$app->language);
//        \Yii::$app->on(Application::EVENT_AFTER_REQUEST, self::class.'::beforeRequest');
    }
}