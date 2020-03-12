<?php
/**
 * User: zura
 * Date: 8/27/18
 * Time: 3:53 PM
 */

namespace intermundia\yiicms\web;


use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\Page;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class View
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
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
        if (Yii::$app->websiteContentTree) {
            $this->website = Yii::$app->websiteContentTree->getModel();
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
            $pageTranslation = $page->getActiveTranslation();
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
}
