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
 * @property integer $loginAttemptCount
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\web
 */
class User extends \yii\web\User
{
    /**
     * Account suspend time in seconds
     *
     * @var $suspendTime
     */
    public $suspendTime = 15 * 60;
    public $loginAttemptCount = 3;

    public function canEditContent()
    {
        return $this->can(\intermundia\yiicms\models\User::ROLE_EDITOR);
    }
}
