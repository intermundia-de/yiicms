<?php
/**
 * User: zura
 * Date: 6/25/18
 * Time: 7:06 PM
 */

namespace intermundia\yiicms\web;

/**
 * Class User
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class User extends \yii\web\User
{
    public function canEditContent()
    {
        return $this->can(\intermundia\yiicms\models\User::ROLE_EDITOR);
    }
}
