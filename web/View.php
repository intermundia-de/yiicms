<?php
/**
 * User: zura
 * Date: 8/27/18
 * Time: 3:53 PM
 */

namespace intermundia\yiicms\web;


use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\Page;
use intermundia\yiicms\models\WebsiteTranslation;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Class View
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class View extends \yii\web\View
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var \intermundia\yiicms\models\ContentTree
     */
    public $contentTreeObject = false;

    /**
     * @var \intermundia\yiicms\models\Website
     */
    private $website = false;

    private $meta_tags = null;

    /**
     * Get find the root element in content tree and return it. Assuming that root element must be Website
     *
     * @return \intermundia\yiicms\models\Website|null
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getWebsite()
    {
        if ($this->website === false) {
            /** @var ContentTree $contentTree */
            $contentTree = \frontend\models\ContentTree::find()->roots()->one();
            $this->website = $contentTree->getModel();
        }

        return $this->website;
    }

    public function getOgImage()
    {
        return count($this->getWebsite()->activeTranslation->og_image) ?
            $this->getWebsite()->activeTranslation->og_image[0]->getUrl() : null;
    }

    public function getOgSitename()
    {
        return count($this->getWebsite()->activeTranslation->og_image) ?
            $this->getWebsite()->activeTranslation->og_image[0]->getUrl() : null;
    }

    public function getMetaTags()
    {
        if ($this->meta_tags === null && Yii::$app->pageContentTree) {
            /** @var Page $page */
            $page = Yii::$app->pageContentTree->getModel();
            $pageTranslation = $page->getActiveTranslation()->one();
            $this->meta_tags = [
                'meta_title' => $pageTranslation->meta_title ?: $pageTranslation->title,
                'meta_description' => $pageTranslation->meta_description,
                'meta_keywords' => $pageTranslation->meta_keywords,
                'language' => Yii::$app->language
            ];
        }
        return $this->meta_tags;
    }


    public function getMetaTag($metaTagName)
    {
        return ArrayHelper::getValue($this->getMetaTags(), $metaTagName);
    }

    public function getCorporateData()
    {
        /**
         * @var $website WebsiteTranslation
         */
        $website = Yii::$app->websiteContentTree->getModel()->activeTranslation;
        $url = Yii::getAlias('@frontendUrl/');

        $data = [
            "@context" => "http://schema.org",
            "@type" => "Organization",
            '@id' => $url,
            "url" => $url,
            "name" => $website->og_site_name ? $website->og_site_name : $website->title ? $website->title : $website->name,
        ];

        if ($website->short_description) {
            $data["description"] = $website->short_description;
        }

        if ($website->logo_image) {
            $data["logo"] = $website->logo_image[0]->getUrl();
        }

        if ($website->address_of_company) {
            $data["address"] = [
                'type' => "PostalAddress",
                'addressCountry' => $website->company_country,
                'addressLocality' => $website->company_city,
                'streetAddress' => $website->address_of_company,
                'postalCode' => $website->company_postal_code
            ];
        }

        if ($website->contact_type && $website->telephone) {
            $data["contactPoint"] = [
                "@type" => "ContactPoint",
                "contactType" => $website->contact_type,
                "telephone" => $website->telephone
            ];
        }

        if ($website->company_business_hours) {
            if (!array_key_exists("location", $data)) {
                $data["location"] = [
                    "@type" => "Place",
                ];
            }

            $businessHours = Json::decode($website->company_business_hours);

            array_walk($businessHours, function (&$shedule, $day) {
                $shedule = ['dayOfWeek' => $day,
                    'opens' => $shedule['startTime'], 'closes' => $shedule['endTime']];
            });
            $data["location"]["OpeningHoursSpecification"] = array_values($businessHours);
        }

        if ($website->location_latitude && $website->location_longitude) {
            if (!array_key_exists("location", $data)) {
                $data["location"] = [
                    "@type" => "Place",
                ];
            }

            $data["location"]["geo"] = [
                "@type" => "GeoCoordinates",
                "latitude" => $website->location_latitude,
                "longitude" => $website->location_longitude,
            ];
        }
        if (array_key_exists("address", $data)) {
            $data["location"]["address"] = $data["address"];
        }

        if ($website->social_links) {
            $data["sameAs"] = explode(',', $website->social_links);
        }

        return Json::encode($data);
    }
}
