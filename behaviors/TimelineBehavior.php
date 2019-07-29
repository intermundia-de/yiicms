<?php
/**
 * User: zura
 * Date: 6/23/18
 * Time: 11:27 AM
 */

namespace intermundia\yiicms\behaviors;


use intermundia\yiicms\commands\AddToTimelineCommand;
use intermundia\yiicms\models\BaseTranslateModel;
use intermundia\yiicms\modules\timeline\models\TimelineEvent;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class TimelineBehavior
 * @package intermundia\yiicms\behaviors
 */
class TimelineBehavior extends Behavior
{

    /** @var BaseTranslateModel */
    public $owner;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    /**
     *
     */
    public function afterSave($event)
    {
        $baseTranslateModel = $this->owner;
        $foreignKeyName = $baseTranslateModel->getForeignKeyNameOnModel();

        \Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'group' => 'content',
            'category' => $baseTranslateModel->getModelClass()::getFormattedTableName(),
            'event' => $event->name === ActiveRecord::EVENT_AFTER_INSERT ? TimelineEvent::EVENT_CREATE : TimelineEvent::EVENT_UPDATE,
            'record_id' => $baseTranslateModel->$foreignKeyName,
            'record_name' => $baseTranslateModel->getTitle(),
            'data' => [
                'new' => $baseTranslateModel->getData(),
                'old' => $baseTranslateModel->oldAttr
            ],
            'createdBy' => \Yii::$app->user->id
        ]));
    }


}
