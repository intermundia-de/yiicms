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
 * @property integer $suspendTime
 * @property integer $loginATtemptCount
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class User extends \yii\web\User
{
    public $suspendTime = 60 * 60 * 24;
    public  $loginATtemptCount = 3;

    public function canEditContent()
    {
        return $this->can(\intermundia\yiicms\models\User::ROLE_EDITOR);
    }
}
