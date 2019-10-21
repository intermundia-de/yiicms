<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\components\NestedSetModel;
use intermundia\yiicms\models\query\ContentTreeQuery;
use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Url;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "{{%content_tree}}".
 *
 * @property int                      $id
 * @property int                      $record_id
 * @property string                   $table_name
 * @property string                   $content_type
 * @property string                   $website
 * @property int                      $lft
 * @property int                      $rgt
 * @property int                      $depth
 * @property int                      $link_id
 * @property int                      $created_at
 * @property int                      $created_by
 * @property int                      $updated_at
 * @property int                      $updated_by
 * @property int                      $deleted_at
 * @property int                      $deleted_by
 * @property int                      $hide
 * @property string                   $view
 * @property string                   $key
 * @property array                    $custom_class
 * @property int                      $show_as_sibling
 *
 * @method makeRoot( $runValidation = true, $attributes = null )
 * @method prependTo( $node, $runValidation = true, $attributes = null )
 * @method appendTo( $node, $runValidation = true, $attributes = null )
 * @method insertBefore( $node, $runValidation = true, $attributes = null )
 * @method insertAfter( $node, $runValidation = true, $attributes = null )
 * @method deleteWithChildren
 * @method ContentTreeQuery parents( $depth = null )
 * @method ContentTreeQuery children( $depth = null )
 * @method ContentTreeQuery leaves
 * @method prev
 * @method next
 * @method isRoot
 * @method isChildOf
 * @method isLeaf
 * @method beforeInsert
 * @method afterInsert
 *
 * @property ContentTree              $link
 * @property ContentTreeTranslation   $activeTranslation  Translation of `Yii::$app->language`
 * @property ContentTreeTranslation   $defaultTranslation Translation of `Yii::$app->sourceLanguage`
 * @property ContentTreeTranslation   $currentTranslation `activeTranslation` or `defaultTranslation`
 * @property ContentTreeTranslation   $linkActiveTranslation
 * @property ContentTreeTranslation[] $translations
 * @property User                     $updatedBy
 * @property User                     $createdBy
 * @property Search[]                 $searchModels
 */
class ContentTree extends \yii\db\ActiveRecord
{
    const TABLE_NAME_WEBSITE = 'website';
    const TABLE_NAME_PAGE = 'page';
    const TABLE_NAME_VIDEO_SECTION = 'video_section';
    const TABLE_NAME_CONTENT_TEXT = 'content_text';
    const TABLE_NAME_SECTION = 'section';
    const TABLE_NAME_CAROUSEL = 'carousel';
    const TABLE_NAME_CAROUSEL_ITEM = 'carousel_item';
    const TABLE_NAME_FILE_MANAGER_ITEM = 'file_manager_item';

    const EVENT_ALIAS_CHANGED = 'aliasChanged';

    private $_alias = null;

    private $closestPage = false;

    static $aliasMap = false;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_tree}}';
    }

    public function behaviors()
    {
        return [
            'timestamp' => TimestampBehavior::class,
            'blameable' => BlameableBehavior::class,
            'tree' => [
                'class' => NestedSetsBehavior::class,
                'treeAttribute' => 'website',
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['record_id', 'table_name'], 'required'],
            [
                [
                    'record_id',
                    'link_id',
                    'lft',
                    'rgt',
                    'depth',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                    'deleted_at',
                    'deleted_by',
                    'hide'
                ],
                'integer'
            ],
            [['custom_class', 'show_as_sibling'], 'safe'],
            [['view'], 'string', 'max' => 64],
            [['table_name', 'content_type'], 'string', 'max' => 255],
            [['key'], 'string', 'max' => 1024],
            [['show_as_sibling'], 'integer', 'max' => 1],
            [['view'], 'default', 'value' => ''],
        ];
    }

    /**
     * @param $insert
     * @return array|bool|string
     */
    public function beforeSave($insert)
    {
        $this->custom_class = is_array($this->custom_class) ? implode(',', $this->custom_class) : '';

        return parent::beforeSave($insert);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'record_id' => Yii::t('intermundiacms', 'Record ID'),
            'table_name' => Yii::t('intermundiacms', 'Table'),
            'key' => Yii::t('intermundiacms', 'Key'),
            'view' => Yii::t('intermundiacms', 'View'),
            'lft' => Yii::t('intermundiacms', 'Lft'),
            'rgt' => Yii::t('intermundiacms', 'Rgt'),
            'depth' => Yii::t('intermundiacms', 'Depth'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'created_by' => Yii::t('intermundiacms', 'Created By'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
            'updated_by' => Yii::t('intermundiacms', 'Updated By'),
            'deleted_at' => Yii::t('intermundiacms', 'Deleted At'),
            'deleted_by' => Yii::t('intermundiacms', 'Deleted By'),
            'hide' => Yii::t('intermundiacms', 'Show/Hide'),
            'custom_class' => Yii::t('intermundiacms', 'Custom Css Class'),
            'show_as_sibling' => Yii::t('intermundiacms', 'Display as Sibling'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        return $this->hasMany(ContentTreeTranslation::class, ['content_tree_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSearchModels()
    {
        return $this->hasMany(Search::class, ['content_tree_id' => 'id']);
    }

    /**
     * @return \intermundia\yiicms\models\ContentTreeTranslation
     */
    public function getActiveTranslation()
    {
        return $this->currentTranslation ?: $this->defaultTranslation ?: $this->translations[0];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultTranslation()
    {
        return $this->hasOne(ContentTreeTranslation::class, ['content_tree_id' => 'id'])
            ->andWhere([ContentTreeTranslation::tableName() . '.language' => Yii::$app->websiteMasterLanguage]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrentTranslation()
    {
        return $this->hasOne(ContentTreeTranslation::class, ['content_tree_id' => 'id'])
            ->andWhere([ContentTreeTranslation::tableName() . '.language' => Yii::$app->language]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLinkActiveTranslation()
    {
        return $this->hasOne(ContentTreeTranslation::class,
            ['content_tree_id' => 'link_id'])->andWhere(['language' => Yii::$app->language]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranslation()
    {
        return $this->hasOne(ContentTreeTranslation::class, ['content_tree_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLink()
    {
        return $this->hasOne(static::class, ['id' => 'link_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContentTreeQuery the active query used by this AR class.
     */
    public static function find($alias = null)
    {
        $alias = $alias ?: ContentTree::tableName();

        return ( new \intermundia\yiicms\models\query\ContentTreeQuery(get_called_class()) )
            ->forRoot(Yii::$app->websiteContentTree->id, $alias);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContentTreeQuery the active query used by this AR class.
     */
    public static function findClean()
    {
        return new \intermundia\yiicms\models\query\ContentTreeQuery(get_called_class());
    }

    public function getAlias()
    {
        if ($this->_alias === null) {
            $this->_alias = $this->getActualItemActiveTranslation()->alias;
        }

        return $this->_alias;
    }


    /**
     *
     *
     * @param array $fields
     * @param array $extraFields
     * @param array $appendParams
     * @return array
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public static function getItemsAsTree(
        $fields = ['id', 'alias_path', 'name' => 'label', 'url', 'record_id', 'language'],
        $extraFields = [],
        $appendParams = []
    )
    {
        $contentTreeItems = ContentTree::findBySql("
            SELECT `content_tree`.`id`,
                   `content_tree`.`record_id`,
                   `content_tree`.`link_id`,
                   `content_tree`.`table_name`,
                   `content_tree`.`lft`,
                   `content_tree`.`rgt`,
                   `content_tree`.`depth`,
                   `content_tree`.`hide`,
                   IFNULL(ct.alias_path, IFNULL(ctt.alias_path, (SELECT alias_path FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `alias_path`,
                   IFNULL(ct.name, IFNULL(ctt.name, (SELECT `name` FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `name`,
                   IFNULL(ct.short_description, IFNULL(ctt.short_description, (SELECT short_description FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `short_description`,
                   IFNULL(ct.language, IFNULL(ctt.language, (SELECT language FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `language`
            FROM `content_tree`
                     LEFT JOIN `content_tree_translation` `ct` ON
                ct.content_tree_id = `content_tree`.id AND ct.language = :language
                     LEFT JOIN `content_tree_translation` `ctt` ON
                ctt.content_tree_id = `content_tree`.id AND ctt.language = :masterLanguage
            WHERE (`content_tree`.`website` = :website)
              AND (`content_tree`.`deleted_at` IS NULL)
            ORDER BY `content_tree`.`lft`
        ", [
            'website' => Yii::$app->websiteContentTree->id,
            'language' => \Yii::$app->language,
            'masterLanguage' => \Yii::$app->websiteMasterLanguage
        ])->asArray()->all();
        $nestedSetModel = new NestedSetModel($contentTreeItems, $extraFields, $appendParams);
        $items = $nestedSetModel->getTree($fields);

        return [$items];
    }

    /**
     *
     *
     * @param string $menuKey
     * @param array $fields
     * @param array $extraFields
     * @param array $appendParams
     * @return array
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public static function getMenuItemsAsTree(
        $menuKey,
        $fields = ['id', 'alias_path', 'name' => 'label', 'url', 'record_id', 'language'],
        $extraFields = [],
        $appendParams = []
    )
    {
        $contentTreeItems = ContentTree::findBySql("
            SELECT `content_tree`.`id`,
                   `content_tree`.`record_id`,
                   `content_tree`.`link_id`,
                   `content_tree`.`table_name`,
                   `content_tree`.`lft`,
                   `content_tree`.`rgt`,
                   `content_tree`.`depth`,
                   `content_tree`.`hide`,
                   IFNULL(ct.alias_path, IFNULL(ctt.alias_path, (SELECT alias_path FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `alias_path`,
                   IFNULL(ct.name, IFNULL(ctt.name, (SELECT `name` FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `name`,
                   IFNULL(ct.short_description, IFNULL(ctt.short_description, (SELECT short_description FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `short_description`,
                   IFNULL(ct.language, IFNULL(ctt.language, (SELECT language FROM content_tree_translation WHERE content_tree_id = content_tree.id LIMIT 1))) AS `language`
            FROM `content_tree`
                     LEFT JOIN `content_tree_translation` `ct` ON
                ct.content_tree_id = `content_tree`.id AND ct.language = :language
                     LEFT JOIN `content_tree_translation` `ctt` ON
                ctt.content_tree_id = `content_tree`.id AND ctt.language = :masterLanguage
                     INNER JOIN content_tree_menu m ON 
                content_tree.id = m.content_tree_id
                INNER JOIN menu m2 ON m.menu_id = m2.id
            WHERE (`content_tree`.`website` = :website)
              AND (`content_tree`.`deleted_at` IS NULL)
              AND `m2`.`key` = :menuKey
            ORDER BY `content_tree`.`lft`
        ", [
            'menuKey' => $menuKey,
            'website' => Yii::$app->websiteContentTree->id,
            'language' => \Yii::$app->language,
            'masterLanguage' => \Yii::$app->websiteMasterLanguage
        ])->asArray()->all();
        $nestedSetModel = new NestedSetModel($contentTreeItems, $extraFields, $appendParams);
        $items = $nestedSetModel->getTree($fields);

        return [$items];
    }

    /**
     * @param null  $id
     * @param null  $tableNames
     * @param null  $lft
     * @param null  $rgt
     * @param array $fields
     * @param array $extraFields
     * @param array $appendParams
     * @return array
     * @throws \Exception
     */
    public static function getItemsAsTreeForLink(
        $id = null,
        $tableNames = null,
        $lft = null,
        $rgt = null,
        $fields = ['id', 'table_name' => 'type', 'alias_path' => 'path', 'name' => 'text', 'items' => 'children'],
        $extraFields = [],
        $appendParams = []
    )
    {
        $query = ContentTree::find()->tree();

        if ($lft !== null && $rgt !== null) {
            $query->andWhere(['<', 'content_tree.deleted_at', $lft])
                ->andWhere(['>', 'content_tree.link_id', $rgt]);
        }

//        if ($id) {
//            $query->andWhere(['<>', 'content_tree.id', $id]);
//        }

        if ($tableNames) {
            array_push($tableNames, 'website');
            $query->andWhere(['content_tree.table_name' => $tableNames]);
        }


        $contentTreeItems = $query->asArray()->all();

        $nestedSetModel = new NestedSetModel($contentTreeItems, $extraFields, $appendParams, 'children');
        $items = $nestedSetModel->getTree($fields);

        return [$items];
    }

    public static function getIdAliasMap($customCache = false, $language = null)
    {
        $cache = $customCache ?: Yii::$app->cache;
        $language = $language ?: Yii::$app->language;

        $key = self::getAliasMapCacheKey($language);

        if (!ArrayHelper::getValue(self::$aliasMap, $language)){
            if (!$cache->exists($key)){
                self::$aliasMap[$language] = self::getAliasMapData($language);
                $cache->set($key, self::$aliasMap[$language]);
            } else {
                self::$aliasMap[$language] = $cache->get($key);
            }
        } else {
            $cache->set($key, self::$aliasMap[$language]);
        }
        return self::$aliasMap[$language];
    }

    public static function invalidateAliasMap($customCache = false, $language = null)
    {
        $cache = $customCache ?: Yii::$app->cache;
        $language = $language ?: Yii::$app->language;

        $key = self::getAliasMapCacheKey($language);
        $cache->delete($key);
        self::$aliasMap[$language] = false;
    }

    public static function getAliasMapCacheKey($language)
    {
        return ['alias_map_' . $language];
    }

    public static function getAliasMapData($language)
    {
        $db = \Yii::$app->getDb();
        $command = $db->createCommand(
            "SELECT c.id,
IFNULL(CONCAT(GROUP_CONCAT(IFNULL(IFNULL(part.alias, part2.alias), part3.alias) ORDER BY par.lft SEPARATOR '/'), '/',
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

        $command->bindParam(":currentLanguage", $language);
        $command->bindParam(":masterLanguage", \Yii::$app->websiteMasterLanguage);

        $data = $command->queryAll();
        $data = ArrayHelper::map($data, 'id', 'alias_path');

        return $data;
    }

    /**
     * Return a BaseModel instance
     *
     * @return BaseModel
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getModel()
    {
        $baseModel = ArrayHelper::getValue(Yii::$app->baseModelObjects, $this->content_type . '.' . $this->record_id);
        if (!$baseModel) {
            $className = Yii::$app->contentTree->getClassName($this->content_type);
            if ($className) {
                return $className::find()->byId($this->record_id)->one();
            }
        }

        return $baseModel;
    }

    public function getMenuTreeModel()
    {
        $model = ContentTreeMenu::find()->byContentTreeId($this->id)->all();

        return ArrayHelper::map($model, 'menu_id', function ($model) {
            return $model->content_tree_id;
        });

    }

    public function getTreeId()
    {
        $tree = ContentTree::find()->byRecordIdTableName($this->record_id, $this->table_name)->linkedIdIsNull()->one();

        return $tree->id;
    }


    public function getDirectChildren()
    {
        return $this->children(1);
    }

    /**
     * @return ContentTreeQuery
     */
    public function getLinkLocationParents()
    {
        return ContentTree::find('ct')
            ->select('ctp.*')
            ->alias('ct')
            ->leftJoin(ContentTree::tableName() . ' ctp',
                'ctp.lft < ct.lft AND ctp.rgt > ct.rgt AND ctp.depth = ct.depth - 1 AND ctp.website = :website',
                ['website' => Yii::$app->websiteContentTree->id])
            ->byLinkId($this->id, 'ct')
            ->orderBy('ctp.lft')
            ->notDeleted('ct');
    }

    public function getFirstDepthChildren()
    {
        return $this->children(0);
    }

    /**
     * @param array $tableNames
     * @return ContentTreeQuery
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getItemsQuery(
        $tableNames = [
            ContentTree::TABLE_NAME_VIDEO_SECTION,
            ContentTree::TABLE_NAME_SECTION,
            ContentTree::TABLE_NAME_CONTENT_TEXT,
            ContentTree::TABLE_NAME_CAROUSEL,
            ContentTree::TABLE_NAME_CAROUSEL_ITEM,
        ]
    )
    {
        return $this->children(1)->andWhere(['table_name' => $tableNames]);
    }

    public function getParentsQuery()
    {
        return $this->parents($this->depth - 1);
    }

    public function getParentsQueryWithRoot()
    {
        return $this->parents($this->depth);
    }

    public function getTableName()
    {
        return implode(" ", array_map('ucfirst', explode('_', $this->table_name)));
    }

    public function getNodes()
    {
        return $this->activeTranslation->alias_path;
    }

    public function getFullUrl($asArray = false, $schema = false)
    {
        $url = ['content-tree/index', 'nodes' => $this->getNodes(), 'language' => $this->activeTranslation->language];

        return $asArray ? $url : Url::to($url, $schema);
    }

    /**
     * Generates and returns url for object
     *
     * @param bool $asArray
     * @param bool $schema
     * @return array|string
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getUrl($asArray = false, $schema = false)
    {
        return $this->getUrlForLanguage(Yii::$app->language, $asArray, $schema);
//
//        $aliasMap = ContentTree::getIdAliasMap();
//        $aliasPath = ArrayHelper::getValue($aliasMap, $this->id, '');
//        $defaultAliasPath = ArrayHelper::getValue($aliasMap, Yii::$app->defaultContent->id, '');
//
//        $url = ['content-tree/index', 'nodes' => $aliasPath];
//        $url = $asArray ? $url : Url::to($url, $schema);
//
//        $defaultUrl = ['content-tree/index', 'nodes' => $defaultAliasPath];
//        $defaultUrl = $asArray ? $defaultUrl : Url::to($defaultUrl, $schema);
//
//        if ($defaultUrl === $url) {
//            return $asArray ? ['content-tree/index', 'nodes' => ''] : '/';
//        }
//
//        return $url;
    }

    public function getUrlForLanguage($languageCode, $asArray = false, $schema = false)
    {
        if ($this->id == Yii::$app->defaultContentId){
            return $asArray ? ['content-tree/index', 'nodes' => ''] : Url::to('/', $schema);
        }
        $aliasMap = ContentTree::getIdAliasMap(false, $languageCode);
        $aliasPath = ArrayHelper::getValue($aliasMap, $this->id, '');

        $url = ['content-tree/index', 'nodes' => $aliasPath];
        $url = $asArray ? $url : Url::to($url, $schema);

        return $url;
    }

    public function getPage()
    {
        if ($this->table_name === self::TABLE_NAME_PAGE) {
            return $this;
        }
        if ($this->closestPage === false) {
            $this->closestPage = $this->parents()
                ->andWhere(['table_name' => self::TABLE_NAME_PAGE])
                ->orderBy('depth DESC')
                ->limit(1)
                ->one();
        }

        return $this->closestPage;
    }

    public function getPageUrl($includeSectionAlias = false, $schema = false)
    {
        $pageParent = $this->getPage();
        if (!$pageParent) {
            return null;
        }
        $url = $pageParent->getUrl(false, $schema);
        if ($includeSectionAlias && $this->table_name !== self::TABLE_NAME_PAGE) {
            $url .= "#id_{$this->id}";
        }

        return $url;
    }

    public function getBackendFullUrl()
    {
        $language = Yii::$app->language;
        return Yii::getAlias('@backendUrl/content/' . $language . '/website/') . $this->getNodes();
    }

    public function getFrontendUrl()
    {
        return Yii::getAlias('@frontendUrl/') . $this->getFrontendPath();
    }

    public function getName()
    {
        return $this->activeTranslation->name;
    }

    public function getParent($depth = 1)
    {
        return $this->parents($depth)->one();
    }

    public function getParentId()
    {
        return $this->getParent() ? $this->getParent()->id : null;
    }


    public function getCssClass()
    {
        return "content-{$this->table_name} content-{$this->content_type} content-{$this->table_name}-" . ( $this->view ?: 'default' )
            . " content-{$this->content_type}-" . ( $this->view ?: 'default' )
            . " content-{$this->table_name}-" . $this->id . ( $this->hide == 1 ? ' content-hidden' : '' )
            . " content-{$this->table_name}-{$this->getAlias()}" . " content-{$this->table_name}-{$this->key}";
    }

    /**
     * @return bool
     */
    public function hasCustomViews()
    {
        return Yii::$app->contentTree->hasCustomViews($this->table_name);
    }

    /**
     * @return array
     */
    public function getViews()
    {
        return Yii::$app->contentTree->getViewsForTable($this->content_type);
    }

    public function getEditableAttributes($attribute, $type = '', $editable = true)
    {
        if (Yii::$app->user->canEditContent()) {
            $editableContent = $editable === true ? 'data-editable=true  contenteditable=true' : '';

            // @TODO We need to consider parent_id also later
            return $editableContent . ' data-language="' . $this->activeTranslation->language . '" data-content-id="' . $this->id . '" data-type="' . $type . '" data-backend-url="' . $this->getBackendFullUrl() . '" data-attr="' . $attribute . '"';
        }

        return '';
    }

    public function getEditableAttributesForSection($type)
    {
        if (Yii::$app->user->canEditContent()) {
            // @TODO We need to consider parent_id also later
            return ' data-language="' . Yii::$app->language . '" data-content-id="' . $this->id . '" data-title="' . $this->getActualItem()->activeTranslation->name . '" data-type="' . $type . '" data-backend-url="' . $this->getBackendFullUrl() . '"';
        }

        return '';
    }

    public function getLinkedObject($tableName = [])
    {
        $query = $this->getDirectChildren();
        if ($tableName) {
            $query->andWhere([self::tableName() . '.table_name' => $tableName]);
        }

        return $query->andWhere(['is not', self::tableName() . '.link_id', null])
            ->notDeleted()
            ->one();
    }

    public function getBreadCrumbs()
    {
        /** @var ContentTree[] $items */
        $items = array_merge($this->getParentsQueryWithRoot()->all(), [$this]);
        foreach ($items as $node) {
            $name = $node->getActualItemActiveTranslation()->name;
            $breadCrumbs[] = ['name' => $name, 'url' => $node->getFullUrl()];
        }

        return $breadCrumbs;
    }

    public function getActualItem()
    {
        if ($this->link_id) {
            return $this->link;
        }

        return $this;
    }

    public function getActualItemActiveTranslation()
    {
        return $this->activeTranslation;
    }

    /**
     * @param $language
     * @return string
     */
    public function getFrontendPath(): string
    {
        $nodes = $this->getNodes();
        if (strpos($nodes, '/')) {
            $nodesArray = explode('/', $nodes);
            array_shift($nodesArray);
            $nodes = implode('/', $nodesArray);
        } else {
            if (strlen($nodes) === 0) {
                $nodes = '';
            }
        }

        return $nodes;
    }

    /**
     * @param $language
     * @return string
     * @deprecated Will be removed in next release. Use [getPage()->getNodes()] method
     */
    public function getFrontendEditingPath(): string
    {
        $nodes = '';
        if ($this->table_name === self::TABLE_NAME_PAGE) {
            $nodes = $this->getNodes();
        } else {
            $parent = $this->parents(1)->one();
            while ($parent) {
                if ($parent->table_name === self::TABLE_NAME_PAGE) {
                    $nodes = $parent->getNodes();
                    break;
                }
                $parent = $parent->parents(1)->one();
            }
        }
        if (strpos($nodes, '/')) {
            $nodesArray = explode('/', $nodes);
            array_shift($nodesArray);
            $nodes = implode('/', $nodesArray);
        } else {
            if (strlen($nodes) === 0) {
                $nodes = '';
            }
        }

        return $nodes;
    }

    public function deleteTree()
    {
        $this->deleted_at = time();
        $this->deleted_by = Yii::$app->user->id;
        $this->save();
    }

    public function deleteWithChild()
    {
        $this->deleteTree();
        $children = $this->children()->all();
        if ($children) {
            foreach ($children as $child) {
                $child->deleteTree();
            }
        }
    }


    /**
     * @param string $footerKey
     * @param array  $tableNames
     * @param null   $depth
     * @return ContentTree[]|array
     *
     * @deprecated Will be removed in v3.0.0. Use [[getItemsForMenu]] instead
     */
    public static function getFooterItems(
        $footerKey = 'footer',
        $tableNames = [
            ContentTree::TABLE_NAME_WEBSITE,
            ContentTree::TABLE_NAME_PAGE
        ],
        $depth = null
    )
    {

        return self::getItemsForMenu($footerKey, $tableNames, $depth);
    }


    /**
     * @param       $key
     * @param array $tableNames
     * @param null  $depth
     * @return ContentTree[]|array
     */
    public static function getItemsForMenu(
        $key,
        $tableNames = [
            ContentTree::TABLE_NAME_WEBSITE,
            ContentTree::TABLE_NAME_PAGE
        ],
        $depth = null
    )
    {

        $ct = ContentTree::tableName();
        $ctm = ContentTreeMenu::tableName();
        $query = ContentTree::find()
            ->innerJoin($ctm,
                $ctm . '.content_tree_id = ' . $ct . '.id')
            ->innerJoin(Menu::tableName(), Menu::tableName() . '.id = ' . $ctm . '.menu_id')
            ->leftJoinOnTranslation()
            ->notHidden()
            ->andWhere([
                $ct . '.table_name' => $tableNames
            ])
            ->with([
                'defaultTranslation',
                'currentTranslation'
            ])
            ->andWhere([
                Menu::tableName() . '.key' => $key,
            ]);

        if ($depth !== null) {
            $query->andWhere([$ct . '.depth' => $depth]);
        }

        return $query->orderBy($ct . '.lft')->all();
    }

    /**
     * @return array|ContentTree|null
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getClosestPage()
    {
        return $this->parents()
            ->andWhere([self::tableName() . '.table_name' => self::TABLE_NAME_PAGE])
            ->orderBy('lft ASC')
            ->limit(1)
            ->one();
    }

    public function getUpdatedByUsername()
    {
        return $this->updatedBy ? $this->updatedBy->username : null;
    }

    /**
     * @return mixed
     */
    public function getCustomCssClassList($table_name = null)
    {
        return Yii::$app->contentTree->getCustomCssClass($this->table_name ? $this->table_name : strtolower($table_name));
    }

    /**
     * @return string
     */
    public function getCustomCssClass()
    {
        $objectModelClasses = explode(',', $this->custom_class);
        $class = '';
        foreach ($objectModelClasses as $objectModelClass) {
            $class .= $objectModelClass . ' ';
        }

        return $class;
    }

    public function getContentType()
    {
        return Inflector::camel2words($this->content_type);
    }

    public function hasDefaultView()
    {
        return !$this->view;
    }

    /**
     * Delete ContentTree item with its base model and with children items
     *
     * @return bool
     * @throws \yii\db\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function deleteWithBaseModel()
    {
        $children = array_merge($this->children()->orderBy('lft DESC')->all(), [$this]);

        $transaction = Yii::$app->db->beginTransaction();
        foreach ($children as $child) {
            if (!$child->delete()) {
                Yii::error('Unable to delete ContentTree: ' . $child->id);
                $transaction->rollBack();

                return false;
            }
        }

        $transaction->commit();

        return true;
    }

    public function beforeDelete()
    {
        if (!$this->link_id) {
            $baseModel = $this->getModel();
            if (!$baseModel) {
                Yii::warning("Base model does not exist for ContentTree: " . $this->id);
            } elseif ($baseModel && $baseModel->delete()) {
                Yii::info("ContentTree with base model was deleted");
            }
        }

        return parent::beforeDelete();
    }

    public function linkInside(ContentTree $parentTree, ContentTree $linkFrom)
    {
//        $parentTreeTranslations = ArrayHelper::index($parentTree->translations, 'language');
        $this->link_id = $linkFrom->id;
        $this->record_id = $linkFrom->record_id;
        $this->table_name = $linkFrom->table_name;
        $this->content_type = $linkFrom->content_type;
        if (!$this->appendTo($parentTree)) {
            throw new Exception("Linking did not work: Errors: " . VarDumper::dumpAsString($this->errors));
        }

        foreach ($linkFrom->translations as $translation) {
//            if (!isset($parentTreeTranslations[$translation->language])) {
//                continue;
//            }
            $data = $translation->toArray();
            $parentTreeTranslation = $parentTree->activeTranslation;
            unset($data['id']);
            $newTranslation = new ContentTreeTranslation();
            $newTranslation->load($data, '');
            $newTranslation->content_tree_id = $this->id;
            $newTranslation->alias_path = $parentTreeTranslation->alias_path . '/' . $newTranslation->alias;
            $newTranslation->getBehavior('sluggable')->onlyMakeUniqueInPath = true;
            if (!$newTranslation->save()) {
                throw new Exception("Linking did not work: Errors: " . VarDumper::dumpAsString($newTranslation->errors));
            }
        }

        return true;
    }

    /**
     * Check if current object is linked in $contentTree
     *
     * @param \intermundia\yiicms\models\ContentTree $contentTree
     * @return array|\intermundia\yiicms\models\ContentTree|null
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function isLinkedInside(ContentTree $contentTree)
    {
        return $contentTree->children(1)->byLinkId($this->id)->one();
    }

    /**
     * Return the display name of the content_type
     *
     * @return mixed
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getDisplayContentType()
    {
        return Yii::$app->contentTree->getDisplayName($this->content_type);
    }
}
