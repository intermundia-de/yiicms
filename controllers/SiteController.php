<?php

namespace intermundia\yiicms\controllers;

use Yii;
use common\models\ContentTree;
use intermundia\yiicms\formatters\SitemapXmlResponseFormatter;
use intermundia\yiicms\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Class SiteController
 *
 * @author Mirian Jintchvelashvili
 * @package frontend\controllers
 */
class SiteController extends Controller
{
    public function actionSitemapXml($language = null) {
        if($language) {
            $websiteLanguages = array_keys(Yii::$app->websiteLanguages);
            $contentLanguages = array_map(function($lang) {
                $separatorPos = strpos($lang, '-');
                if($separatorPos > -1) {
                    return substr($lang, 0, $separatorPos);
                }
                else {
                    return $lang;
                }
            }, $websiteLanguages);

            if(!in_array($language, $contentLanguages)) {
                throw new NotFoundHttpException();
            }
        }
        $siteMapXmlFormatter = new SitemapXmlResponseFormatter();

        Yii::$app->response->format = 'sitemap_xml';
        Yii::$app->response->formatters['sitemap_xml'] = $siteMapXmlFormatter;

        $items = ContentTree::find()
            ->notHidden()
            ->notDeleted()
            ->inSitemap()
            ->andWhere('table_name = :page')
            ->params([
                'page' => ContentTree::TABLE_NAME_PAGE,
            ])
            ->orderBy('lft')
            ->all();

        $sitemapItems = [];
        foreach ($items as $item) {
            $sitemapItems[] = [
                'loc' => $item->getFullUrl(false, true),
                'changefreq' => 'daily',
                'priority' => 1 - ($item->depth - 1) / 10
            ];
        }
        return $sitemapItems;
    }
}