<?php
/**
 * User: zura
 * Date: 3/1/19
 * Time: 1:18 PM
 */
namespace intermundia\yiicms\console;

use intermundia\yiicms\models\ContentTree;

/**
 * Class Application
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\base
 */
class Application extends \yii\console\Application
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var ContentTree
     */
    public $websiteContentTree = null;
    public $pageContentTree = null;
}