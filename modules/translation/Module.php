<?php

namespace intermundia\yiicms\modules\translation;

/**
 * translation module definition class
 */
class Module extends \yii\base\Module
{

    /**
     * @param \yii\i18n\MissingTranslationEvent $event
     */
    public static function missingTranslation($event)
    {
        // do something with missing translation
    }

}
