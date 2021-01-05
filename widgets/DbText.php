<?php

namespace intermundia\yiicms\widgets;

use intermundia\yiicms\models\WidgetText;
use Yii;
use yii\base\Widget;

/**
 * Class DbText
 * Return a text block content stored in db
 * @package intermundia\yiicms\widgets
 */
class DbText extends Widget
{
    /**
     * @var string text block key
     */
    public $key;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var WidgetText
     */
    private $model = null;

    public $cacheDuration = 60 * 60 * 2;

    public function init()
    {
        $this->getModel();
        parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        $cacheKey = [
            WidgetText::class,
            $this->key,
            Yii::$app->language
        ];
        $content = Yii::$app->cache->get($cacheKey);
        if (!$content) {
            $model = $this->getModel();
            if ($model && ($activeTranslation = $model->activeTranslation)) {
                $content = $activeTranslation->body;
                Yii::$app->cache->set($cacheKey, $content, $this->cacheDuration);
            }
        }
        return $content;
    }


    public function getModel()
    {
        if (!$this->model) {
            $this->model = WidgetText::findOne(['key' => $this->key, 'status' => WidgetText::STATUS_ACTIVE]);
        }
        return $this->model;
    }
}
