<?php

namespace intermundia\yiicms\traits;

use yii\base\InvalidConfigException;

trait MultiDomainTrait
{
    public function getWebsiteDomains($websiteKey)
    {
        return array_merge($this->getFrontendDomains($websiteKey), $this->getBackendDomains($websiteKey));
    }

    public function getFrontendDomains($websiteKey)
    {
        $domains = \Yii::$app->multiSiteCore->websites[$websiteKey]['domains'];
        if (!array_key_exists('frontend', $domains)) {
            throw new InvalidConfigException('Multisite config "domains" array must contain sub-array with "frontend" key');
        }
        return $domains['frontend'];
    }

    public function getBackendDomains($websiteKey)
    {
        $domains = \Yii::$app->multiSiteCore->websites[$websiteKey]['domains'];
        if (!array_key_exists('frontend', $domains)) {
            throw new InvalidConfigException('Multisite config "domains" array must contain sub-array with "backend" key');
        }
        return $domains['backend'];
    }
}

?>