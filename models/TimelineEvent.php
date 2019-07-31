<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\TimelineEventQuery;
use intermundia\yiicms\models\User;
use intermundia\yiicms\models\ContentTree;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * This is the model class for table "timeline_event".
 *
 * @property integer $id
 * @property string $website_key
 * @property string $application
 * @property string $group
 * @property string $category
 * @property string $event
 * @property integer $record_id
 * @property string $record_name
 * @property string $data
 * @property string $created_at
 * @property integer $created_by
 *
 *
 * @property User $createdBy
 */
class TimelineEvent extends ActiveRecord
{
    const EVENT_CREATE = 'create';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';
    const EVENT_ARCHIVE = 'archive';
    const EVENT_DESIGN_CHANGE = 'design_change';
    const EVENT_POSITION = 'position';
    const EVENT_USER_SIGNUP = 'signup';
    const SHOW = 'show';
    const HIDE = 'hide';

    const GROUP_USER = 'user';
    const GROUP_CONTENT = 'content';

    const CATEGORY_CONTENT_TREE = 'content_tree';
    const CATEGORY_USER = 'content_tree';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%timeline_event}}';
    }

    /**
     * @return TimelineEventQuery
     */
    public static function findClean()
    {
        return new TimelineEventQuery(get_called_class());
    }

    /**
     * @return TimelineEventQuery
     */
    public static function find()
    {
        return (new TimelineEventQuery(get_called_class()))
            ->forWebsite(\Yii::$app->websiteKey);
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => null
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['application', 'category', 'event'], 'required'],
            [['data'], 'safe'],
            [['created_by', 'record_id'], 'integer'],
            [['application', 'group', 'record_name', 'category', 'event'], 'string', 'max' => 64],
            ['website_key', 'string', 'max' => 1024],
            [
                ['created_by'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['created_by' => 'id']
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->data = Json::decode($this->data, true);
        parent::afterFind();
    }

    /**
     * @return string
     */
    public function getFullEventName()
    {
        return sprintf('%s.%s', $this->category, $this->event);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getIcon()
    {
        $icon = \Yii::$app->contentTree->getIcon($this->category);
        if (!$icon) {
            if ($this->event === self::EVENT_DESIGN_CHANGE) {
                return 'fa-paint-brush';
            }

            if ($this->event === self::EVENT_DESIGN_CHANGE) {
                return 'fa-paint-brush';
            }
        }
        return $icon;
    }

    public function getDisplayText()
    {
        $content = \Yii::$app->contentTree->getDisplayName($this->category);
        return \Yii::t('backend', '{user} {action} {content} {record_name}', [
            'user' => Html::a($this->getCreatorPublicIdentity(), $this->getCreatorUrl(), ['class' => 'text-danger']),
            'action' => $this->getUserFriendlyAction($this->event),
            'content' => $content,
            'record_name' => Html::a(strip_tags($this->record_name), $this->getRecordUrl(), ['class' => 'text-danger'])
        ]);
    }

    public function getCreatorUrl()
    {
        return ['/user/view', 'id' => $this->created_by];
    }

    public function getRecordUrl()
    {
        if ($this->event === TimelineEvent::EVENT_DELETE) {
            return '';
        }
        if ($this->group === 'content') {
            $object = $this->getObject();
            if (!$object) {
                return '';
            }
            if (!($object instanceof ContentTree)){
                $object = $object->contentTree;
            }
            if (!$object) {
                return '';
            }

//            return $object->getFullUrl();
            $url = $object->getFullUrl();
            return str_replace('/core/', '/', $url);
        }
        return '';
    }

    public function getObject()
    {
        if ($this->group === 'content') {
            if ($this->category === 'content_tree') {
                return ContentTree::find()->byId($this->record_id)->one();
            } else {
                /** @var BaseModel $className */
                $className = \Yii::$app->contentTree->getClassName($this->category);
                return $className::find()->byId($this->record_id)->one();
            }
        }
        return null;
    }

    public function userFriendlyActions()
    {
        return [
            'create' => \Yii::t('backend', 'created'),
            'update' => \Yii::t('backend', 'updated'),
            'delete' => \Yii::t('backend', 'deleted'),
            'archive' => \Yii::t('backend', 'archived'),
            'design_change' => \Yii::t('backend', 'changed the design of'),
            'position' => \Yii::t('backend', 'changed position of'),
            'show' => \Yii::t('backend', 'set shown'),
            'hide' => \Yii::t('backend', 'set hidden'),
        ];
    }

    public function getUserFriendlyAction($action)
    {
        return ArrayHelper::getValue($this->userFriendlyActions(), $action);
    }

    /**
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param int $count
     * @return array|TimelineEvent[]|ActiveRecord[]
     */
    public static function getLatestNItems($count = 20)
    {
        return self::find()->orderBy('created_at DESC')->today()->limit($count)->all();
    }

    public function getDisplayDate()
    {
        return \Yii::$app->formatter->asRelativeTime($this->created_at);
    }

    public function getCreatorAvatar()
    {
        return $this->createdBy ? $this->createdBy->userProfile->getAvatar() : '';
    }

    public function getCreatorPublicIdentity()
    {
        return $this->createdBy ? $this->createdBy->getPublicIdentity() : '';
    }
}
