<?php

namespace intermundia\yiicms\widgets;

use intermundia\yiicms\helpers\LanguageHelper;
use intermundia\yiicms\models\Language;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;

/**
 * Class FrontendLanguageSelector
 *
 * Renders language selector
 * according to multisite_websites "domains" configuration
 *
 * @author  Mirian Jintchvelashvili
 * @package intermundia\yiicms\widgets
 */
class FrontendLanguageSelector extends Nav
{
    private $languageDomains;
    private $currentLanguage;
    /**
     * @var $dropDownParentUrl string
     * URL for parent Dropdown 'a' tag
     */
    public $dropDownParentUrl;

    /**
     * @var $dropDownParentUrl string
     * text for parent Dropdown 'a' tag
     */
    public $dropDownParentText;

    /**
     * @var $dropDownParentOptions [] | null
     * HTML options for parent 'li' tag
     */
    public $dropDownParentOptions;
    /**
     * @var $dropDownOptions [] | null
     * HTML options for children 'ul' tag
     */
    public $dropDownOptions;
    /**
     * @var $dropDownParentLinkOptions [] | null
     * HTML options for parent DropDown 'a' tag,
     * except 'href' attribute.
     * Use $dropDownParentUrl to change 'href' attribute
     */
    public $dropDownParentLinkOptions;
    /**
     * @var $renderAsDropDown bool
     * Render available language items whether dropdown or
     * direct 'li' tags
     */
    public $renderAsDropDown = true;
    /**
     * @var $displayCurrentLanguage bool
     * Include currently active language in available language list.
     * This option makes sense when $renderAsDropDown = false
     */
    public $displayCurrentLanguage = false;
    /**
     * @var $excludeLanguages []
     * Array of 2 character language codes to exclude from render list
     */
    public $excludeLanguages = ['en'];

    private function resolveLanguageDomains()
    {
        $this->languageDomains = \Yii::$app->getFrontendDomains(Yii::$app->websiteContentTree->key);
        $multipleDomainPerLanguage = array_count_values($this->languageDomains);

        $checkHosts = $multipleDomainPerLanguage ? ( max(array_values($multipleDomainPerLanguage)) > 1 ) : false;
        $languages = ArrayHelper::index(
            Language::find()->byCode(array_values($this->languageDomains))
            ->cache(10000)->all(), 'code');
        foreach ($this->languageDomains as $languageDomain => $langCode) {
            if ($checkHosts) {
                //Check if domain host matches requested url host
                $domainHost = parse_url("//$languageDomain", PHP_URL_HOST);
                $requestHost = parse_url(\Yii::$app->request->getAbsoluteUrl(), PHP_URL_HOST);

                if ($domainHost != $requestHost || in_array(LanguageHelper::convertLongCodeIntoShort($langCode), $this->excludeLanguages)) {
                    unset($this->languageDomains[$languageDomain]);
                    continue;
                }
            }

            if (in_array(LanguageHelper::convertLongCodeIntoShort($langCode), $this->excludeLanguages)) {
                unset($this->languageDomains[$languageDomain]);
                continue;
            }


            if (!isset($languages[$langCode])) {
                throw new \yii\base\Exception("No record with code=\"{$langCode}\" found in \"language\" table.");
            }
            $this->languageDomains[$languageDomain] = $languages[$langCode];
//            if ($langCode == Yii::$app->websiteMasterLanguage) {
//                $masterLanguageDomain = true;
//            }
        }
    }

    private function getItemsForLanguagePicker()
    {
        $protocol = Yii::$app->request->getIsSecureConnection() ? 'https' : 'http';
        $items = [];
        $item = [];
        $currentPage = Yii::$app->pageContentTree;

        foreach ($this->languageDomains as $url => $language) {
            $item = [
                'label' => $language->name,
                'url' => $protocol . '://' . $url . ($currentPage ? $currentPage->getUrlForLanguage($language->code) : '')
            ];
            if ($this->currentLanguage) {
                if ($language->code == $this->currentLanguage->code) {
                    if (!$this->displayCurrentLanguage) {
                        continue;
                    } else {
                        $item['active'] = true;
                    }
                }
            }
            $items[] = $item;
        }
        if (!$this->currentLanguage) {
            return $items;
        }
        $parentItem = [
            'label' => $this->dropDownParentText ?: $this->currentLanguage->name,
            'items' => $items,
            'linkOptions' => $this->dropDownParentLinkOptions,
            'dropDownOptions' => $this->dropDownOptions];
        if ($this->dropDownParentUrl) {
            $parentItem['url'] = $this->dropDownParentUrl;
        }
        if ($this->dropDownParentOptions) {
            $parentItem['options'] = $this->dropDownParentOptions;
        }
        if ($this->renderAsDropDown) {
            return [$parentItem];
        } else {
            return $items;
        }
    }

    public function init()
    {
        $this->resolveLanguageDomains();
        $this->items = $this->getItemsForLanguagePicker();
        parent::init();
    }
}
