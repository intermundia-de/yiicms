<?php
/**
 * User: zura
 * Date: 8/2/19
 * Time: 3:56 PM
 */

namespace intermundia\yiicms\bundle;


use yii\web\AssetBundle;

/**
 * Class BackendLoginAsset
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\bundle
 */
class BackendLoginAsset extends AssetBundle
{
    public $sourcePath = '@cmsCore/assets/backend/login';

    public $css = [
        'login.css'
    ];
}