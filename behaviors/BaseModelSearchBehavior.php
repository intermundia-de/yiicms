<?php
/**
 * User: zura
 * Date: 6/23/18
 * Time: 11:27 AM
 */

namespace intermundia\yiicms\behaviors;


use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\Search;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class SearchBehavior
 * @package intermundia\yiicms\behaviors
 */
class BaseModelSearchBehavior extends Behavior
{

    /** @var BaseModel */
    public $owner;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseModel::EVENT_AFTER_CONTENT_TREE_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     *
     */
    public function afterInsert()
    {
        $baseModel = $this->owner;
        $searchableAttributes = $this->getSearchableAttributes();

        foreach ($searchableAttributes as $searchableAttribute) {
            $data[] = [
                'content_tree_id' => $baseModel->contentTree->id,
                'table_name' => $this->owner->getFormattedTableName(),
                'record_id' => $this->owner->id,
                'language' => null,
                'attribute' => $searchableAttribute,
                'content' => strip_tags($baseModel->attributes[$searchableAttribute])
            ];
        }

        if (isset($data)) {
            Search::batchInsert($data);
        }
    }

    /**
     *
     */
    public function afterUpdate()
    {
        $baseModel = $this->owner;
        $searchableAttributes = $this->getSearchableAttributes();
        $searches = Search::find()
            ->byContentTreeId($baseModel->contentTree->id)
            ->andWhere(['language' => null])
            ->all();

        $attributes = array_map(function ($search) {
            return $search->attribute;
        }, $searches);

        $editedAttributes = array_diff($searchableAttributes, $attributes);
        $deletedAttributes = array_diff($attributes, $searchableAttributes);

        if (count($editedAttributes) > 0) {
            $this->insertSearch($editedAttributes);
        }

        if (count($deletedAttributes) > 0) {
            Search::deleteAll(['attribute' => $deletedAttributes, 'table_name' => $baseModel->getFormattedTableName(), 'language' => null]);
        }

        foreach ($searches as $search) {
            if (!in_array($search->attribute,
                    $deletedAttributes) && $search->content != strip_tags($baseModel->attributes[$search->attribute])) {
                Search::updateAll(['content' => strip_tags($baseModel->attributes[$search->attribute])],
                    'id = ' . $search->id);
            }
        }
    }


    public function getSearchableAttributes()
    {
        return array_intersect($this->owner->attributes(),
            \Yii::$app->contentTree->getSearchableAttributes($this->owner->getFormattedTableName(), true));
    }
    /**
     *
     * @param $searchableAttributes
     * @throws \yii\db\Exception
     */
    public function insertSearch($searchableAttributes)
    {
        foreach ($searchableAttributes as $searchableAttribute) {
            $data[] = [
                'content_tree_id' => $this->owner->contentTree->id,
                'table_name' => $this->owner->getFormattedTableName(),
                'record_id' => $this->owner->id,
                'language' => null,
                'attribute' => $searchableAttribute,
                'content' => strip_tags($this->owner->attributes[$searchableAttribute])
            ];
        }
        if (isset($data)) {
            Search::batchInsert($data);
        }
    }
}
