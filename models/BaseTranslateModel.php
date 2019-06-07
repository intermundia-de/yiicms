<?php
/**
 * Created by PhpStorm.
 * User: guga
 * Date: 6/19/18
 * Time: 9:07 PM
 */

namespace intermundia\yiicms\models;


use intermundia\yiicms\behaviors\ContentChangeListener;
use intermundia\yiicms\behaviors\ContentListener;
use intermundia\yiicms\behaviors\SearchBehavior;
use intermundia\yiicms\behaviors\StorageUrlBehavior;
use intermundia\yiicms\behaviors\TimelineBehavior;
use intermundia\yiicms\commands\AddToTimelineCommand;
use Yii;
use yii\base\Event;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class BaseTranslateModel
 *
 * @property string $language
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\models
 *
 * @property boolean $titleChanged
 * @property array $oldAttr
 *
 * @property BaseModel $baseModel
 * @property ContentTree $contentTree
 */
abstract class BaseTranslateModel extends ActiveRecord
{
    public $alias;
    public $alias_path;
    public $parentContentId;
    public $contentTreeId;
    public $treeName;
    public $foreignKeyName;
    public $tableName;

    public $titleChanged = false;
    public $oldAttr = [];

    const CHANGE_TITLE = 'changeTitle';
    const ADD_TO_SEARCH = 'addToSearch';
    const ADD_TIMELINE = 'addTimeLine';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
        ];
    }

    public function behaviors()
    {
        /** @var BaseModel $baseModel */
        $baseModel = $this->getModelClass();
        return [
            ContentChangeListener::class,
            SearchBehavior::class,
            TimelineBehavior::class,
            [
                'class' => StorageUrlBehavior::class,
                'columnNames' => Yii::$app->contentTree->getSearchableAttributes($baseModel::getFormattedTableName())
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBaseModel()
    {
        /** @var BaseModel $class */
        $class = $this->getModelClass();
        $foreignKeyName = $this->getForeignKeyNameOnModel();
        return $this->hasOne($class, ['id' => $foreignKeyName]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentTree()
    {
        /** @var BaseModel $class */
        $class = $this->getModelClass();
        $foreignKeyName = $this->getForeignKeyNameOnModel();
        return $this->hasOne(ContentTree::class,
            ['record_id' => $foreignKeyName])->andWhere([ContentTree::tableName() . '.table_name' => $class::getFormattedTableName()])->with('activeTranslation');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSearch()
    {
        $foreignKeyName = $this->getForeignKeyNameOnModel();
        return $this->hasMany(Search::class, ['record_id' => $foreignKeyName]);
    }

    public abstract function getTitle();

    public abstract function getShortDescription();

    public abstract function getModelClass();

    public abstract function getForeignKeyNameOnModel();

    public abstract function getData();

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->checkTitleChanged();
        $this->oldAttr = $this->oldAttributes;
        return parent::beforeSave($insert);
    }

    /** Set $thistitleChanged true if title has changed
     *  or baseTranslateModel is a new record
     */
    public function checkTitleChanged()
    {
        // Temporary save new title and attributes
        $newTitle = $this->getTitle();
        $newAttributes = $this->attributes;
        // Load oldAttributes to get oldTitle and compare to newTitle
        $this->load($this->oldAttributes, '');
        $this->titleChanged = $newTitle !== $this->getTitle() || !$this->oldAttributes;
        // Load new attributes again
        $this->load($newAttributes, '');
    }

    /**
     *
     *
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \trntv\bus\exceptions\MissingHandlerException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function afterSave($insert, $changedAttributes)
    {
        /* If title has Changed trigger titleChange event.
         *  This event update own and children's alias_path,file_manager_item's path,
         *  rename file folder name oldAlias to newAlias
         */
        $this->titleChanged && $this->trigger(self::CHANGE_TITLE);

        $contentTreeTranslation = $this->contentTree->getTranslation()->andWhere(['language' => $this->language])->one();
        $this->alias_path = $contentTreeTranslation ? $contentTreeTranslation->alias_path : null;

        parent::afterSave($insert, $changedAttributes);
    }


    public function getModelClassName()
    {
        $className = explode('\\', $this->getModelClass());
        return end($className);
    }

    /**
     *
     *
     * @param $fileManagerFilename
     * @param $index
     * @return string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getUrlForFile($fileManagerFilename, $index = 0)
    {
        /** @var FileManagerItem $fileManagerFile */
        $fileManagerFile = ArrayHelper::getValue($this, $fileManagerFilename);
        if ($fileManagerFile) {
            return $fileManagerFile[$index]->getUrl();
        }
        return '';
    }

    /**
     * Get attribute from FileManagerItem
     *
     * @param $fileManagerFilename
     * @param $attr
     * @return mixed|string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getAttrForFile($fileManagerFilename, $attr)
    {
        /** @var FileManagerItem $fileManagerFile */
        $fileManagerFile = ArrayHelper::getValue($this, $fileManagerFilename);
        if ($fileManagerFile) {
            return $fileManagerFile->$attr;
        }
        return '';
    }

    /**
     * Render attribute
     *
     * @param $attr
     * @return mixed|string
     **/


    public function renderAttribute($attr)
    {
        $content = $this->$attr;
        $contentTreeId = '';
        $contentTree = '';
        $keys = [];
        $ids = [];
        $tree = [];

        $match = preg_match('/\{\{content:(\d+)\}\}/', $content, $result);
        if ($match) {
            $contentTreeId = $result[1];
            $contentTree = ContentTree::find()->byId($contentTreeId)->notHidden()->notDeleted()->all();
            foreach ($contentTree as $item) {
                $tree['{{content:' . $item->id . '}}'] = Yii::$app->view->render('@frontend/views/design/' . $item->table_name . '/' . ($item->view ? $item->view : 'default'),
                    [
                        'contentTreeItem' => $item,
                        'model' => $item->getModel()
                    ]);
            }
            return str_replace(array_keys($tree), array_values($tree), $content);
        }
        return $content;
    }
}
