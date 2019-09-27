<?php

namespace intermundia\yiicms\models;

use apollo11\envAnalyzer\helpers\FileHelper;
use intermundia\yiicms\behaviors\ContentChangeListener;
use intermundia\yiicms\behaviors\SluggableBehavior;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%content_tree_translation}}".
 *
 * @property int $id
 * @property int $content_tree_id
 * @property string $language
 * @property string $alias
 * @property string $name
 * @property string $alias_path
 * @property string $short_description
 * @property array $_oldContentTreeAttributes
 *
 * @property boolean $selfUpdateOnly
 *
 * @property ContentTree $contentTree
 */
class ContentTreeTranslation extends \yii\db\ActiveRecord
{

    public $key;
    /**
     * {@inheritdoc}
     */
    const STORAGE_PATH = '@storage/web/source/';
    public $oldAlias;
    public $oldAliasPath;
    public $children;
    public $move = 0;
    private $_oldContentTreeAttributes;


    /*
     * When true, updates alias and alias_path,
     * Doesn't update children items */
    public $selfUpdateOnly = false;

    const CHANGE_ALIAS_PATH = 'changeAliasPath';
    const CHANGE_CHILDREN_PATH = 'changeChildrenPath';

    public static function tableName()
    {
        return '{{%content_tree_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_tree_id', 'language'], 'required'],
            [['content_tree_id'], 'integer'],
            [['language'], 'string', 'max' => 64],
            [['name', 'alias'], 'string', 'max' => 255],
            [['short_description'], 'string', 'max' => 1024],
            [['alias_path'], 'string', 'max' => 2048],
            [['key'], 'string', 'max' => 1024],
            [['oldAlias'], 'string', 'max' => 2048],
            [['oldAliasPath'], 'string', 'max' => 2048],
            [
                ['content_tree_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ContentTree::class,
                'targetAttribute' => ['content_tree_id' => 'id']
            ],
//            ['alias_path', 'unique', 'targetAttribute' => ['alias_path', 'language']]
        ];
    }

    public function behaviors()
    {
        return [
            'sluggable' => [
                'class' => SluggableBehavior::class,
                'slugAttribute' => 'alias',
                'attribute' => 'name',
                'immutable' => false,
                'ensureUnique' => false,
                'forceUpdate' => function () {
                    return $this->selfUpdateOnly;
                },
                'replaceWords' => [
                    'ä' => 'ae',
                    'Ä' => 'ae',
                    'ö' => 'oe',
                    'Ö' => 'oe',
                    'ü' => 'ue',
                    'Ü' => 'ue',
                    'ß' => 'ss',
                    '<br>' => '',
                    '®' => '',
                    '/' => '',
                    '<sup>' => '',
                    '</sup>' => '',
                    '<strong>' => '',
                    '</strong>' => '',
                    '<h1>' => '',
                    '</h1>' => '',
                    '<h2>' => '',
                    '</h2>' => '',
                    '<h3>' => '',
                    '</h3>' => '',
                    '<h4>' => '',
                    '</h4>' => '',
                ]
            ],
            ContentChangeListener::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'content_tree_id' => Yii::t('intermundiacms', 'Content Tree ID'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'alias' => Yii::t('intermundiacms', 'Alias'),
            'alias_path' => Yii::t('intermundiacms', 'Alias Path'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'short_description' => Yii::t('intermundiacms', 'Short Description'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentTree()
    {
        return $this->hasOne(ContentTree::class, ['id' => 'content_tree_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContentTreeTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\ContentTreeTranslationQuery(get_called_class());
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->_oldContentTreeAttributes = $this->oldAttributes;

        return parent::beforeSave($insert);
    }

    /**
     *
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\base\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */


    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->selfUpdateOnly) {
            $aliasChanged = $this->_oldContentTreeAttributes && $this->_oldContentTreeAttributes['alias_path'] != $this->oldAttributes['alias_path'];
            if ($insert || $aliasChanged) {
                $this->setChildren();
            }

            /* If alias has changed trigger CHANGE_ALIAS_PATH event.
            *  This event update own alias_path and own file_manager_items' path,
            *  rename file folder name oldAlias to newAlias
            */
            $aliasChanged && !$this->contentTree->link_id && $this->trigger(self::CHANGE_ALIAS_PATH);

            /* If translation creates and it has children trigger CHANGE_CHILDREN_PATH event.
            *  This event updates children's alias_path,file_manager_item's path,
            */
            $insert && $this->children && !$this->contentTree->link_id && $this->contentTree->depth > 0 && $this->trigger(self::CHANGE_CHILDREN_PATH);
        }
        parent::afterSave($insert, $changedAttributes);

        if ($aliasChanged) {
            \Yii::$app->frontendCache->delete(self::getAliasMapCacheKey());
            self::setAliasMapCache(\Yii::$app->frontendCache);
        }
    }

    public static function getAliasMapCacheKey()
    {
        return ['alias_map_' . Yii::$app->language];
    }

    public static function setAliasMapCache($cache)
    {
        $db = \Yii::$app->getDb();
        $command = $db->createCommand(
            "SELECT c.id,
IFNULL(CONCAT(GROUP_CONCAT(IFNULL(IFNULL(part.alias, part2.alias), part3.alias) SEPARATOR '/'), '/',
              IFNULL(IFNULL(ctt.alias, ctt2.alias), ctt3.alias)),
       IFNULL(IFNULL(ctt.alias, ctt2.alias), ctt3.alias)) as alias_path
FROM content_tree c
         LEFT JOIN content_tree par on par.lft < c.lft AND par.rgt > c.rgt AND par.table_name != 'website'
         LEFT JOIN content_tree_translation ctt on c.id = ctt.content_tree_id AND ctt.language = :currentLanguage
         LEFT JOIN content_tree_translation ctt2 on c.id = ctt2.content_tree_id AND ctt2.language = :masterLanguage
         LEFT JOIN (SELECT * FROM content_tree_translation ctt GROUP BY ctt.content_tree_id) ctt3
                   ON ctt3.content_tree_id = c.id
         LEFT JOIN content_tree_translation part on par.id = part.content_tree_id AND part.language = :currentLanguage
         LEFT JOIN content_tree_translation part2 on par.id = part2.content_tree_id AND part2.language = :masterLanguage
         LEFT JOIN (SELECT * FROM content_tree_translation ctt GROUP BY ctt.content_tree_id) part3
                   ON part3.content_tree_id = par.id
                   
WHERE c.table_name != 'website'
GROUP BY c.id
ORDER BY par.lft;");

        $command->bindParam(":currentLanguage", \Yii::$app->language);
        $command->bindParam(":masterLanguage", \Yii::$app->websiteMasterLanguage);

        $content = $command->queryAll();
        $content = ArrayHelper::map($content, 'id', 'alias_path');

        $cache->set(self::getAliasMapCacheKey(), $content);
    }

    /**
     * Sets children with parent's translation path
     */
    public function setChildren()
    {
        $contentTree = $this->contentTree;
        $this->children = $contentTree->children()
            ->select(['link_id', 'table_name', 'record_id', ContentTree::tableName() . '.id as content_tree_id', "SUBSTRING_INDEX(alias_path, '/', $contentTree->depth ) as parent_old_path"])
            ->leftJoin(ContentTreeTranslation::tableName() . ' t',
                't' . '.content_tree_id = ' . ContentTree::tableName() . ".id AND t.language = :language", [
                    'language' => $this->language
                ])
            ->asArray()
            ->all();
    }

    /**
     * @return bool
     */
    public function getCorrectFileManagerPath()
    {
        if (!$this->getOldAliasPath()) {
            $this->setOldAliasPath($this->_oldContentTreeAttributes['alias_path']);
        }

        $oldDirectory = $this->getFileManagerDirectoryPath($this->_oldContentTreeAttributes['alias_path']);
        if (file_exists($oldDirectory)) {
            return $this->getOldAliasPath();
        }

        return false;
    }

    /**
     * @param $aliasPath
     */
    public function setOldAliasPath($aliasPath)
    {
        $this->oldAliasPath = $aliasPath;
    }

    public function getOldAliasPath()
    {
        return $this->oldAliasPath;
    }

    /**
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function updateOwnFileManagerItems()
    {
        $contentTree = $this->contentTree;
        $oldAliasPath = $this->getCorrectFileManagerPath();
        if (!$oldAliasPath) {
            return;
        }

        Yii::$app->db->createCommand("UPDATE " . FileManagerItem::tableName() . " SET 
                path = REPLACE(path, :oldAliasPath , :alias_path ) 
                WHERE table_name = :table_name AND record_id = :record_id AND language = :language ",
            [
                ':oldAliasPath' => $oldAliasPath,
                ':alias_path' => $this->alias_path,
                ':table_name' => $contentTree->table_name,
                ':record_id' => $contentTree->record_id,
                ':language' => $this->language,
            ]
        )->execute();
    }


    /**
     * @throws \yii\db\Exception
     */
    public function updateChildrenAliasPath()
    {
        /** @var ContentTree $contentTree */
        $contentTree = $this->contentTree;
        $contentTreeIds = implode(',', ArrayHelper::getColumn($this->children, 'content_tree_id'));

        //Update childrens alias_path
        Yii::$app->db->createCommand("UPDATE " . ContentTreeTranslation::tableName() . " SET 
                alias_path = REPLACE(alias_path, SUBSTRING_INDEX(alias_path, '/', :depth) , :alias_path ) 
                WHERE content_tree_id in ($contentTreeIds) AND language = :language ",
            [
                ':depth' => $contentTree->depth - $this->move,
                ':alias_path' => $this->alias_path,
                ':language' => $this->language,
            ]
        )->execute();
    }


    /**
     * @throws \yii\db\Exception
     */
    public function updateChildrenFileManagerItem()
    {
        /** @var ContentTree $contentTree */
        $contentTree = $this->contentTree;
        $children = array_filter($this->children, function ($child) {
            return !$child['link_id'];
        });
        if ($children) {
            $sqlArray = array_map(function ($child) {
                return "('" . $child['table_name'] . "' , " . $child['record_id'] . ")";
            }, $children);
            $sqlString = implode(' , ', $sqlArray);
            Yii::$app->db->createCommand("UPDATE " . FileManagerItem::tableName() . " SET 
                path = REPLACE(path, SUBSTRING_INDEX(path, '/', :depth) , :alias_path_with_language )
                WHERE  (( table_name , record_id ) in ( $sqlString ) )  AND language = :language ",
                [
                    ':depth' => $contentTree->depth + 1 - $this->move,
                    ':alias_path_with_language' => $this->language . '/' . $this->alias_path,
                    ':language' => $this->language,
                ]
            )->execute();
        }
    }


    /**
     * @param null $oldDirectoryPath
     * @throws Exception
     */
    public function renameFolder($oldDirectoryPath = null)
    {
        if (!$oldDirectoryPath) {
            $oldAliasPath = $this->getCorrectFileManagerPath();
            if (!$oldAliasPath) {
                return;
            }
            $oldDirectoryPath = $this->getFileManagerDirectoryPath($oldAliasPath);
        }
        //Rename folderName
        if ($oldDirectoryPath && file_exists($oldDirectoryPath)) {
            $newDirectoryPath = $this->getFileManagerDirectoryPath();
            \yii\helpers\FileHelper::createDirectory($newDirectoryPath, 0775, true);
            if ($oldDirectoryPath != $newDirectoryPath) {
                try {
                    \yii\helpers\FileHelper::copyDirectory($oldDirectoryPath, $newDirectoryPath);
                    \yii\helpers\FileHelper::removeDirectory($oldDirectoryPath);
                } catch (\Exception $e) {
                    throw new Exception('Could Not Rename File While updating contentTreeTranslation language:' . $this->language);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getFileManagerDirectoryPath($aliasPath = null)
    {
        $aliasPath = $aliasPath ?: $this->alias_path;

        return Yii::getAlias(FileManagerItem::STORAGE_PATH) . $this->language . '/' . $aliasPath;
    }

    /**
     * Generates a url which contains language also in it
     *
     * @param bool $asArray
     * @param bool $schema
     * @return array|string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getFullUrl($asArray = false, $schema = false)
    {
        $url = ['content-tree/index', 'nodes' => $this->alias_path, 'language' => $this->language];

        return $asArray ? $url : Url::to($url, $schema);
    }

    /**
     * Get url of the object on current language
     *
     * @param bool $asArray
     * @param bool $schema
     * @return array|string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getUrl($asArray = false, $schema = false)
    {
        if (Yii::$app->defaultContentId === $this->contentTree->id) {
            if ($asArray) {
                return $asArray ? ['content-tree/index', 'nodes' => ''] : '/';
            }
        }
        $url = ['content-tree/index', 'nodes' => $this->alias_path];
        $url = $asArray ? $url : Url::to($url, $schema);

        return $url;
    }
}
