<?php
/**
 * User: zura
 * Date: 6/23/18
 * Time: 11:27 AM
 */

namespace intermundia\yiicms\behaviors;


use intermundia\yiicms\models\BaseTranslateModel;
use intermundia\yiicms\models\Search;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class SearchBehavior
 * @package intermundia\yiicms\behaviors
 */
class SearchBehavior extends Behavior
{

    /** @var BaseTranslateModel */
    public $owner;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    /**
     *
     */
    public function afterInsert()
    {
        $baseTranslationModel = $this->owner;
        $searchableAttributes = $this->getSearchableAttributes();

        foreach ($searchableAttributes as $searchableAttribute) {
            $data[] = [
                'content_tree_id' => $baseTranslationModel->contentTree->id,
                'table_name' => $baseTranslationModel->getContentType(),
                'record_id' => $baseTranslationModel->attributes[$baseTranslationModel->getForeignKeyNameOnModel()],
                'language' => $baseTranslationModel->language,
                'attribute' => $searchableAttribute,
                'content' => strip_tags($baseTranslationModel->attributes[$searchableAttribute])
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
        $baseTranslationModel = $this->owner;
        $searchableAttributes = $this->getSearchableAttributes();
        $searches = Search::find()->byContentTreeId($baseTranslationModel->contentTree->id)->byLanguage($baseTranslationModel->language)->all();
        $attributes = array_map(function ($search) {
            return $search->attribute;
        }, $searches);

        $editedAttributes = array_diff($searchableAttributes, $attributes);
        $deletedAttributes = array_diff($attributes, $searchableAttributes);

        if (count($editedAttributes) > 0) {
            $this->insertSearch($editedAttributes);
        }

        if (count($deletedAttributes) > 0) {
            Search::deleteAll(['and', ['attribute' => $deletedAttributes, 'table_name' => $baseTranslationModel->getContentType()], ['not', ['language' => null]]]);
        }

        foreach ($searches as $search) {
            if (!in_array($search->attribute,
                    $deletedAttributes) && $search->content != strip_tags($baseTranslationModel->attributes[$search->attribute])) {
                Search::updateAll(['content' => strip_tags($baseTranslationModel->attributes[$search->attribute])],
                    'id = ' . $search->id);
            }
        }
    }


    public function getSearchableAttributes()
    {
        return array_intersect($this->owner->attributes(),
            \Yii::$app->contentTree->getSearchableAttributes($this->owner->getContentType()));
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
                'table_name' => $this->owner->getContentType(),
                'record_id' => $this->owner->attributes[$this->owner->getForeignKeyNameOnModel()],
                'language' => $this->owner->language,
                'attribute' => $searchableAttribute,
                'content' => strip_tags($this->owner->attributes[$searchableAttribute])
            ];
        }
        if (isset($data)) {
            Search::batchInsert($data);
        }
    }

}
