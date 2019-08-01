<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\components\NestedSetModel;
use intermundia\yiicms\models\query\ContentTreeQuery;
use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "{{%content_tree}}".
 *
 * @property int $id
 * @property int $record_id
 * @property string $table_name
 * @property string $website
 * @property int $lft
 * @property int $rgt
 * @property int $depth
 * @property int $link_id
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 * @property int $deleted_at
 * @property int $deleted_by
 * @property int $hide
 * @property string $view
 * @property string $key
 * @property array $custom_class
 * @property int $show_as_sibling
 *
 * @method makeRoot($runValidation = true, $attributes = null)
 * @method prependTo($node, $runValidation = true, $attributes = null)
 * @method appendTo($node, $runValidation = true, $attributes = null)
 * @method insertBefore($node, $runValidation = true, $attributes = null)
 * @method insertAfter($node, $runValidation = true, $attributes = null)
 * @method deleteWithChildren
 * @method ContentTreeQuery parents($depth = null)
 * @method ContentTreeQuery children($depth = null)
 * @method ContentTreeQuery leaves
 * @method prev
 * @method next
 * @method isRoot
 * @method isChildOf
 * @method isLeaf
 * @method beforeInsert
 * @method afterInsert
 *
 * @property ContentTree $link
 * @property ContentTreeTranslation $activeTranslation Translation of `Yii::$app->language`
 * @property ContentTreeTranslation $defaultTranslation Translation of `Yii::$app->sourceLanguage`
 * @property ContentTreeTranslation $currentTranslation `activeTranslation` or `defaultTranslation`
 * @property ContentTreeTranslation $linkActiveTranslation
 * @property ContentTreeTranslation[] $translations
 * @property User $updatedBy
 * @property User $createdBy
 * @property Search[] $searchModels
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
            [['table_name'], 'string', 'max' => 255],
            [['key'], 'string', 'max' => 1024],
            [['show_as_sibling'], 'integer', 'max' => 1],
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
            'id' => Yii::t('common', 'ID'),
            'record_id' => Yii::t('common', 'Record ID'),
            'table_name' => Yii::t('common', 'Table'),
            'key' => Yii::t('common', 'Key'),
            'view' => Yii::t('common', 'View'),
            'lft' => Yii::t('common', 'Lft'),
            'rgt' => Yii::t('common', 'Rgt'),
            'depth' => Yii::t('common', 'Depth'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
            'deleted_at' => Yii::t('common', 'Deleted At'),
            'deleted_by' => Yii::t('common', 'Deleted By'),
            'hide' => Yii::t('common', 'Hide'),
            'custom_class' => Yii::t('common', 'Custom Css Class'),
            'show_as_sibling' => Yii::t('common', 'Display as Sibling'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getActiveTranslation()
    {
        if ($this->currentTranslation) {
            return $this->getCurrentTranslation();
        }
        return $this->getDefaultTranslation();
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
        return (new \intermundia\yiicms\models\query\ContentTreeQuery(get_called_class()))
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
        $fields = ['id', 'alias', 'name' => 'label', 'url', 'record_id'],
        $extraFields = [],
        $appendParams = []
    )
    {
        $ct = ContentTreeTranslation::tableName();
        $c = ContentTree::tableName();
        $contentTreeItems = ContentTree::find()
            ->select("
                $c.`id`,
                $c.`record_id`,
                $c.`link_id`,
                $c.`table_name`,
                $c.`lft`,
                $c.`rgt`,
                $c.`depth`,
                $c.`hide`,
                tt.alias as alias,
                tt.name as name,
                tt.`short_description`
            ")->leftJoinOnTranslation()
            ->orderBy($c . '.lft')
            ->notDeleted()
            ->asArray()
            ->all();
        $nestedSetModel = new NestedSetModel($contentTreeItems, $extraFields, $appendParams);
        $items = $nestedSetModel->getTree($fields);
        return [$items];
    }

    /**
     * @param null $id
     * @param null $tableNames
     * @param null $lft
     * @param null $rgt
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

        $query = ContentTree::find()
            ->select([
                'content_tree.id',
                'content_tree.record_id',
                'content_tree.table_name',
                'content_tree.lft',
                'content_tree.rgt',
                'content_tree.depth',
                'content_tree_translation.alias',
                'content_tree_translation.alias_path',
                'content_tree_translation.name as name',
                'content_tree_translation.short_description',
            ])
            ->leftJoin('content_tree_translation', 'content_tree_translation.content_tree_id = content_tree.id')
            ->andWhere(['`content_tree_translation`.language' => \Yii::$app->language])
            ->notDeleted()
//            ->notHidden()
//            ->linkedIdIsNull()
            ->orderBy('content_tree.lft');

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

//    /**
//     *
//     *
//     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
//     * @param array $fields
//     * @param array $extraFields
//     * @param array $appendParams
//     * @return array
//     * @throws \Exception
//     */
//    public static function getItemsAsTreeMenu(
//        $menu_id,
//        $fields = ['id', 'alias', 'name' => 'label', 'url', 'record_id'],
//        $extraFields = [],
//        $appendParams = []
//    ) {
//        $contentTreeItems = ContentTree::findBySql("SELECT
//                          `content_tree`.`id`,
//                          `content_tree`.`record_id`,
//                          `content_tree`.`table_name`,
//                          `lft`,
//                          `rgt`,
//                          `depth`,
//                          `content_tree_translation`.alias,
//                          IFNULL(`content_tree_translation`.`name`, content_tree.table_name) as name,
//                          `content_tree_translation`.`short_description`
//                        FROM `content_tree`
//                          INNER JOIN `content_tree_menu`
//                            ON `content_tree_menu`.content_tree_id = `content_tree`.id AND `content_tree_menu`.menu_id = :menuId
//                          LEFT JOIN `content_tree_translation`
//                            ON `content_tree_translation`.content_tree_id = `content_tree`.id AND `content_tree_translation`.language = :language",
//            [
//                'language' => \Yii::$app->language,
//                'menuId' => $menu_id
//            ])
//            ->asArray()
//            ->all();
//
//        $nestedSetModel = new NestedSetModel($contentTreeItems, $extraFields, $appendParams);
//        $items = $nestedSetModel->getTree($fields);
////        echo '<pre>';
////        var_dump($appendParams, $items);
////        exit;
//
//        return [$items];
//    }

    /**
     * Return a BaseModel instance
     *
     * @return BaseModel
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function getModel()
    {
        $baseModel = ArrayHelper::getValue(Yii::$app->baseModelObjects, $this->table_name . '.' . $this->record_id);
        if (!$baseModel) {
//            echo '<pre>';
//            var_dump("Base model was not found ", $this->table_name . '.' . $this->record_id);
//            echo '</pre>';
            $className = Yii::$app->contentTree->getClassName($this->table_name);
            if ($className) {
                return $className::find()->byId($this->record_id)->one();
            }
        }

        return $baseModel;
    }

    public function getMenuTreeModel()
    {
        $tree = ContentTree::find()->byRecordIdTableName($this->record_id, $this->table_name)->linkedIdIsNull()->one();
        $model = ContentTreeMenu::find()->byContentTreeId($tree->id)->all();

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
        return $this->getActualItemActiveTranslation()->alias_path;
    }

    public function getFullUrl($asArray = false, $schema = false)
    {
        $url = ['content-tree/index', 'nodes' => $this->getNodes()];
        return $asArray ? $url : Url::to($url, $schema);
    }

    public function getUrl($asArray = false, $schema = false)
    {
        $url = ['content-tree/index', 'nodes' => $this->getNodes()];
        $url = $asArray ? $url : Url::to($url, $schema);


        $defaultContent = Yii::$app->defaultContent;
        $defaultUrl = ['content-tree/index', 'nodes' => $defaultContent->getNodes()];
        $defaultUrl = $asArray ? $defaultUrl : Url::to($defaultUrl, $schema);


        if ($defaultUrl === $url) {
            return ['content-tree/index', 'nodes' => ''];
        }

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
        return Yii::getAlias('@backendUrl/content/website/') . $this->getNodes();
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
        return "content-{$this->table_name} content-{$this->table_name}-" . ($this->view ?: 'default')
            . " content-{$this->table_name}-" . $this->id . ($this->hide == 1 ? ' content-hidden' : '')
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
        if ($this->hasCustomViews()) {
            return array_merge([null => Yii::t('backend', 'Default')],
                Yii::$app->contentTree->getViewsForTable($this->table_name));
        } else {
            return [];
        }
    }

    public function getEditableAttributes($attribute, $type = '', $editable = true)
    {
        if (Yii::$app->user->canEditContent()) {
            $editableContent = $editable === true ? 'data-editable=true  contenteditable=true' : '';
            // @TODO We need to consider parent_id also later
            return $editableContent . ' data-language="' . Yii::$app->language . '" data-content-id="' . $this->id . '" data-type="' . $type . '" data-backend-url="' . $this->getBackendFullUrl() . '" data-attr="' . $attribute . '"';
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
     * @param array $tableNames
     * @param null $depth
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
     * @param $key
     * @param array $tableNames
     * @param null $depth
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
}
