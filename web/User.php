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
//    Suspend time in seconds
    public $suspendTime;
    public $loginAttemptCount;

    public function canEditContent()
    {
        return $this->can(\intermundia\yiicms\models\User::ROLE_EDITOR);
    }
}
