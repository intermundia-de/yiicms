<?php
/**
 * User: zura
 * Date: 7/3/18
 * Time: 2:06 PM
 */

namespace intermundia\yiicms\models;

interface BaseModelInterface
{
    public static function getTranslateModelClass();

    public static function getTranslateForeignKeyName();
}