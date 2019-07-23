<?php
/**
 * User: zura
 * Date: 10/17/18
 * Time: 3:04 PM
 */

namespace intermundia\yiicms\widgets;


use yii\bootstrap\Widget;

/**
 * Class ContentEditingHeader
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\widgets
 */
class ContentEditingToolbar extends Widget
{
    public $showLogin = false;

    public function run()
    {
        return $this->render('editing_toolbar.php', [
            'widget' => $this,
        ]);
    }
}