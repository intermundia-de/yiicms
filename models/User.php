<?php

namespace intermundia\yiicms\models;

use DateTime;
use intermundia\yiicms\commands\AddToTimelineCommand;
use intermundia\yiicms\models\query\UserQuery;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $auth_key
 * @property string $access_token
 * @property string $oauth_client
 * @property string $oauth_client_user_id
 * @property string $publicIdentity
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $logged_at
 * @property integer $suspended_till
 * @property integer $login_attempt
 * @property string $password write-only password
 *
 * @property \intermundia\yiicms\models\UserProfile $userProfile
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_NOT_ACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_DELETED = 3;
    const STATUS_SUSPENDED = 4;

    const ROLE_USER = 'user';
    const ROLE_MANAGER = 'manager';
    const ROLE_ADMINISTRATOR = 'administrator';
    const ROLE_EDITOR = 'editor';

    const EVENT_AFTER_SIGNUP = 'afterSignup';
    const EVENT_AFTER_LOGIN = 'afterLogin';
    

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->active()
            ->andWhere(['access_token' => $token])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return User|array|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->active()
            ->andWhere(['username' => $username])
            ->one();
    }

    public function isSuspended()
    {
        return $this->status == self::STATUS_SUSPENDED;
    }

    /**
     * Finds user by username or email
     *
     * @param string $login
     * @return User|array|null
     */
    public static function findByLogin($login)
    {
        return static::find()
            ->active()
            ->andWhere(['or', ['username' => $login], ['email' => $login]])
            ->one();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            'auth_key' => [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => Yii::$app->getSecurity()->generateRandomString()
            ],
            'access_token' => [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'access_token'
                ],
                'value' => function () {
                    return Yii::$app->getSecurity()->generateRandomString(40);
                }
            ]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'oauth_create' => [
                    'oauth_client', 'oauth_client_user_id', 'email', 'username', '!status'
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'unique'],
            [['login_attempt', 'suspended_till'], 'integer'],
            ['status', 'default', 'value' => self::STATUS_NOT_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::statuses())],
            [['username'], 'filter', 'filter' => '\yii\helpers\Html::encode']
        ];
    }

    /**
     * Returns user statuses list
     * @return array|mixed
     */
    public static function statuses()
    {
        return [
            self::STATUS_NOT_ACTIVE => Yii::t('intermundiacms', 'Not Active'),
            self::STATUS_ACTIVE => Yii::t('intermundiacms', 'Active'),
            self::STATUS_DELETED => Yii::t('intermundiacms', 'Deleted'),
            self::STATUS_SUSPENDED => Yii::t('intermundiacms', 'Suspended'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('intermundiacms', 'Username'),
            'email' => Yii::t('intermundiacms', 'E-mail'),
            'status' => Yii::t('intermundiacms', 'Status'),
            'access_token' => Yii::t('intermundiacms', 'API access token'),
            'created_at' => Yii::t('intermundiacms', 'Created at'),
            'updated_at' => Yii::t('intermundiacms', 'Updated at'),
            'logged_at' => Yii::t('intermundiacms', 'Last login'),
            'login_attempt' => Yii::t('intermundiacms', 'Login attempts'),
            'suspended_till' => Yii::t('intermundiacms', 'Suspended Till'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Activate User
     *
     * @return boolean
     */
    public function activate()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->login_attempt = 0;
        $this->suspended_till = 0;
        if (!$this->save()) {
            Yii::error("Could not update user status .user id: $this->id", self::class);
            return false;
        }
        return true;
    }

    /**
     * Suspend User for x Time
     *
     * @return boolean
     */
    public function suspend()
    {
        $this->status = self::STATUS_SUSPENDED;
        $this->suspended_till = time() + Yii::$app->user->suspendTime;
        if (!$this->save()) {
            Yii::error("Could not update user status .user id: $this->id", self::class);
            return false;
        }
        return true;
    }

    /**
     * Increase User login attempts
     *
     * @return boolean
     */
    public function increaseLoginAttempt()
    {
        $this->login_attempt++;
        if (!$this->save()) {
            Yii::error("Could not upadte user login Attempts .user id: $this->id", self::class);
            return false;
        }
        return true;
    }


    /**
     * @return User|string
     * @throws \Exception
     */

    public function getSuspendTime()
    {
        $currentTime = new DateTime('@' . (string)time());
        $suspendedTill = new DateTime('@' . (string)$this->suspended_till);
        $interval = $currentTime->diff($suspendedTill);
        return $interval->format('%Hh %Im %Ss');
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Creates user profile and application event
     * @param array $profileData
     * @throws \Exception
     */
    public function afterSignup(array $profileData = [])
    {
        $this->refresh();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'group' => TimelineEvent::GROUP_USER,
            'category' => TimelineEvent::CATEGORY_USER,
            'event' => TimelineEvent::EVENT_USER_SIGNUP,
            'record_id' => $this->getId(),
            'record_name' => $this->getPublicIdentity(),
            'createdBy' => Yii::$app->user->id,
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        $profile = new UserProfile();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
    }

    /**
     * @return string
     */
    public function getPublicIdentity()
    {
        if ($this->userProfile && $this->userProfile->getFullname()) {
            return $this->userProfile->getFullname();
        }
        if ($this->username) {
            return $this->username;
        }
        return $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
}
