<?php
/**
 * Created by PhpStorm.
 * User: zein
 * Date: 7/3/14
 * Time: 3:14 PM
 */

namespace intermundia\yiicms\bundle;

use backend\assets\BackendAsset;
use yii\web\AssetBundle;

class CKEditorAsset extends AssetBundle
{
    public $sourcePath = '@cmsCore/assets';

    public $js = [
        'js/ck-config.js',
    ];

    public $css = [
        'css/style.css',
    ];

    public $depends = [
        BackendAsset::class,
    ];
}
