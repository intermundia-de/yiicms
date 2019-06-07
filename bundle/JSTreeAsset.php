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

class JSTreeAsset extends AssetBundle
{
    public $sourcePath = '@cmsCore/assets';

    public $js = [
        'jstree/js/jstree.min.js',
    ];

    public $css = [
        'jstree/css/style.less',
    ];

    public $depends = [
        ContentTreeAsset::class,
    ];
}
