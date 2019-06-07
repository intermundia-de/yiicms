<?php
/**
 * Created by PhpStorm.
 * User: guga
 * Date: 12/6/18
 * Time: 5:06 PM
 */

namespace intermundia\yiicms\behaviors;

use yii\db\ActiveRecord;
use yii\base\Behavior;

class StorageUrlBehavior extends Behavior
{
    //Column or array of columns which have richtexteditor data and may contain url
    public $columnNames;
    public $owner;
    public $storageAlias = '@storageUrl/source/';
    public $placeholder = '{{%STORAGE_URL_PLACEHOLDER%}}';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }


    public function beforeSave()
    {
        $columns = $this->columnNames;
        if (is_string($columns)) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = [];
        }
        foreach ($columns as $column) {
            if ($this->owner->{$column}) {
                $this->owner->{$column} = str_replace(\Yii::getAlias($this->storageAlias), $this->placeholder, $this->owner->{$column});
            }
        }
    }


    public function afterFind()
    {
        $columns = $this->columnNames;
        if (is_string($columns)) {
            $columns = [$columns];
        } elseif (!is_array($columns)) {
            $columns = [];
        }
        foreach ($columns as $column) {
            if ($this->owner->{$column}) {
                $this->owner->{$column} = str_replace($this->placeholder, \Yii::getAlias($this->storageAlias), $this->owner->{$column});
            }
        }
    }

}