<?php
namespace intermundia\yiicms\widgets;


use intermundia\yiicms\models\Language;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;

/**
 * Class MultisiteLanguagePicker
 *
 * Renders language picker
 * according to multisite_websites "domains" configuration
 *
 * @author Mirian Jintchvelashvili
 * @package intermundia\yiicms\widgets
 */
class MultisiteLanguagePicker extends Nav
{
    private $languageDomains;
    private $currentLanguage;

    /**
     * @var $dropDownParentUrl string
     * URL for parent Dropdown 'a' tag
     */
    public $dropDownParentUrl;

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

    private function resolveLanguageDomains(){
        $this->languageDomains = array_unique(\Yii::$app->multiSiteCore->websites[Yii::$app->websiteContentTree->key]['domains']);

        foreach ($this->languageDomains as $languageDomain => $langCode) {
            if (substr($langCode, 0, 2) == 'en') {
                unset($this->languageDomains[$languageDomain]);
            } else {

                $language = Language::find()->byCode($langCode)->one();
                if(!$language) {
                    throw new \yii\base\Exception("No record with code=\"{$langCode}\" found in \"language\" table.");
                }
                $this->languageDomains[$languageDomain] = $language;
                if ($langCode == Yii::$app->language) {
                    $this->currentLanguage = $language;
                }
            }
        }
    }

    private function getItemsForLanguagePicker() {
        $protocol = Yii::$app->request->getIsSecureConnection() ? 'https' : 'http';
        $items = [];
        $item = [];
        foreach ($this->languageDomains as $url => $language) {
            $item = [
                'label' => $language->name,
                'url' => $protocol . '://' . $url
            ];

            if ($language->code == $this->currentLanguage->code){
                if(!$this->displayCurrentLanguage) {
                    continue;
                }
                else {
                    $item['active'] = true;
                }
            }

            $items[] = $item;
        }

        $parentItem = [
            'label' => $this->currentLanguage->name,
            'items' => $items,
            'linkOptions' => $this->dropDownParentLinkOptions,
            'dropDownOptions' => $this->dropDownOptions];

        if($this->dropDownParentUrl) {
            $parentItem['url'] = $this->dropDownParentUrl;
        }

        if($this->dropDownParentOptions) {
            $parentItem['options'] = $this->dropDownParentOptions;
        }

        if($this->renderAsDropDown) {
            return [$parentItem];
        }
        else {
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