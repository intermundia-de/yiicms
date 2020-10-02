<?php
/**
 * User: zura
 * Date: 3/5/19
 * Time: 1:53 PM
 */

namespace intermundia\yiicms\web;


use intermundia\yiicms\helpers\LanguageHelper;
use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\Language;
use intermundia\yiicms\models\WidgetText;
use yii\base\Exception;
use yii\base\InvalidCallException;
use yii\caching\DbDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use intermundia\yiicms\traits\MultiDomainTrait;

/**
 * Class BaseApplication
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class BaseApplication extends \yii\web\Application
{
    use MultiDomainTrait;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var ContentTree content_tree table record for table_name page
     */
    public $pageContentTree = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var ContentTree
     */
    public $websiteContentTree = null;

    public $websiteMasterLanguage = null;

    public $websiteLanguages = [];

    public $productionFrontendDomains = [];

    public $hasLanguageInUrl = false;

    /**
     * @var string website key based on multisiteCore websites config
     */
    public $websiteKey = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var string ContentTree::$id of the default page
     */
    public $defaultContentId = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var ContentTree default page ContentTree model record
     */
    public $defaultContent = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var BaseModel[]
     */
    public $baseModelObjects = [];

    public function init()
    {
        $this->on(self::EVENT_BEFORE_REQUEST, [$this, 'beforeRequest']);
        parent::init();
    }

    /**
     * Application before request method
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function beforeRequest()
    {
        $this->resolveLanguageAndWebsite();
    }

    /**
     * Resolve current website key, language and master language for website
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function resolveLanguageAndWebsite()
    {
        foreach (\Yii::$app->multiSiteCore->websites as $websiteKey => $websiteData) {
            $masterLanguage = $websiteData['masterLanguage'];

            $frontendDomains = $this->getFrontendDomains($websiteKey);
            $backendDomains = $this->getBackendDomains($websiteKey);
            $websiteDomains = array_merge($frontendDomains, $backendDomains);

            //Sort website domains based on key length descending order
            uksort($websiteDomains, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            foreach ($websiteDomains as $domain => $langOrConfig) {
                $isFrontendDomain = in_array($domain, array_keys($frontendDomains));

                if (is_string($langOrConfig)) {
                    $lang = $langOrConfig;
                    $isProduction = false;
                } else {
                    $lang = $langOrConfig['language'];
                    $isProduction = ArrayHelper::getValue($langOrConfig, 'isProduction');
                }

                if ($isProduction && $isFrontendDomain) {
                    $this->productionFrontendDomains[$websiteKey][$domain] = $lang;
                }
            }
            foreach ($websiteDomains as $domain => $langOrConfig) {
                 if (is_string($langOrConfig)) {
                    $lang = $langOrConfig;
                } else {
                    $lang = $langOrConfig['language'];
                }
                $isFrontendDomain = in_array($domain, array_keys($frontendDomains));
                //Compare domain host. '//$domain' prevents parse_url failure, since parse_url requires url with schema
                $domainHost = parse_url("//$domain", PHP_URL_HOST);
                if ($domainHost == parse_url($this->request->getAbsoluteUrl(), PHP_URL_HOST)) {
                    $domainPath = parse_url("//$domain", PHP_URL_PATH);
                    $requestUrlParsed = parse_url($this->request->getAbsoluteUrl());
                    $requestPath = $requestUrlParsed['path'];
                    if (!$domainPath) {
                        $domainPath = '/';
                    }
                    /* If no matching path found, the last domain is choosen,
                       since the last domain has no path (after uksort() ordering)
                    */
                    if (preg_match("@^$domainPath/@", $requestPath . '/') || $domainPath == '/') {
                        $this->websiteContentTree = ContentTree::findClean()
                            ->byTableName(ContentTree::TABLE_NAME_WEBSITE)
                            ->byKey($websiteKey)
                            ->one();
                        if (!$this->websiteContentTree) {
                            throw new InvalidCallException("Current website does not exist");
                        }
                        $this->websiteMasterLanguage = $masterLanguage;
                        $this->defaultContentId = $websiteData['defaultContentId'];
                        $this->defaultContent = ContentTree::find()->byId($this->defaultContentId)->one();

                        if (!$this->defaultContent) {
                            throw new InvalidCallException("Default Content for website \"$websiteKey\" does not exist");
                        }
                        $this->language = $lang;
                        $this->websiteLanguages = $this->getWebsiteLanguages($websiteKey);
                        $shortCode = LanguageHelper::convertLongCodeIntoShort($this->language);
                        if (strpos($domain, '/') !== false
                            && (substr($domain, strpos($domain, '/') + 1) === $lang || $shortCode)) {
                            $this->hasLanguageInUrl = true;
                        }
                        $frontendHost = $isFrontendDomain ? $domainHost : ArrayHelper::getValue($websiteData, 'frontendHost');
                        if (!$frontendHost) {
                            \Yii::warning("\"frontendHost\" does not exist for website \"$domain\"");
                            $frontendHost = $domainHost;
                        }

                        \Yii::setAlias('@frontendUrl', $requestUrlParsed['scheme'] . '://' . $frontendHost);
                        \Yii::$app->urlManagerFrontend->setHostInfo(\Yii::getAlias('@frontendUrl'));
                        $this->setHomeUrl($requestUrlParsed['scheme'] . '://' . $domain);
                        $storageUrl = ArrayHelper::getValue($websiteData, 'storageUrl',
                            ($isFrontendDomain ? "" : \Yii::getAlias('@frontendUrl')) . "/storage/web");
                        \Yii::setAlias('@storageUrl', $storageUrl);
                        break;
                    }
                }
            }
        }
        if (!$this->websiteContentTree) {
            throw new Exception('Current domain is not added neither in "frontend" nor in "backend" domain list in multisite config');
        }
    }

    public function getWebsiteLanguages($websiteKey)
    {
        $values = array_values($this->getWebsiteDomains($websiteKey));
        $languageCodes = array_unique(array_map(function ($value) {
            if (is_string($value)) return $value;

            return $value['language'];
        }, $values));

        return ArrayHelper::map(Language::find()->byCode($languageCodes)->asArray()->cache(10000)->all(), 'code', 'name');
    }
}
