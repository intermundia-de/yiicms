<?php

namespace intermundia\yiicms\controllers;

use common\models\ContentTree;
use intermundia\yiicms\formatters\SitemapXmlResponseFormatter;
use intermundia\yiicms\web\BackendController;
use Yii;

/**
 * Site controller
 */
class SiteController extends BackendController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->layout = Yii::$app->user->isGuest || !Yii::$app->user->can('loginToBackend') ? 'base' : 'common';

        return parent::beforeAction($action);
    }

    /**
     * Generates sitemap.xml
     *
     * @author Mirian Jintchvelashvili
     * @return array
     * @throws \yii\base\InvalidConfigException *
     */
    public function actionSitemapXml()
    {
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
                'lastmod' => Yii::$app->formatter->asDate($item->updated_at, 'yyyy-MM-dd'),
                'priority' => 1 - ($item->depth - 1) / 10
            ];
        }
        return $sitemapItems;
    }
}
