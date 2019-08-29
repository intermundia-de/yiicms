<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 6/14/19
 * Time: 8:35 PM
 */

namespace intermundia\yiicms\helpers;


use function GuzzleHttp\Psr7\str;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeMenu;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\FileManagerItem;
use intermundia\yiicms\models\Search;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;

/**
 * Class CopyTranslation
 * @package intermundia\yiicms\helpers
 *
 * @property string $from
 * @property string $to
 * @property array $baseData
 * @property ContentTree $contentTree
 */
class CopyTranslation
{

    public $from;
    public $to;
    public $contentTree;
    public $baseData;
    public $baseClassName;
    public $newAliasPath;
    public $translateClassName;
    /** @var ContentTreeTranslation */
    public $contentTreeTranslationFrom;

    public function __construct($from, $to, $contentTree)
    {
        $this->from = $from;
        $this->to = $to;
        $this->contentTree = $contentTree;

    }

    /**
     * @return array
     */
    public function getBaseData(): array
    {
        return $this->baseData;
    }

    /**
     * @param array $baseData
     */
    public function setBaseData(array $baseData)
    {
        $this->baseData = $baseData;
    }

    public function getBaseClassName()
    {
        if (!$this->contentTree) {
            throw new Exception('Set Content Tree First');
        }
        if ($this->baseClassName) {
            return $this->baseClassName;
        }

        $this->baseClassName = \Yii::$app->contentTree->getClassName($this->contentTree->table_name);

        return $this->baseClassName;
    }

    public function getBaseModel()
    {
        return $this->getBaseClassName()::find()->byId($this->contentTree->record_id)->one();
    }

    public function getBaseTranslateModel()
    {
        return $this->getTranslateClassName()::find()
            ->findByObjectIdAndLanguage($this->getBaseModel()->id, $this->from, $this->getTranslateClassName()::getForeignKeyNameOnModel())
            ->asArray()
            ->one();
    }


    public function getTranslateClassName()
    {
        if ($this->translateClassName) {
            return $this->translateClassName;
        }

        $this->translateClassName = $this->getBaseClassName()::getTranslateModelClass();

        return $this->translateClassName;
    }

    public function copyAll()
    {
        $this->copyBaseTranslation();
        $this->copyContentTreeTranslation();
        $this->copyFileManagerItems();
        $this->copySearch();
    }


    public function copySearch()
    {
        // Copy search items
        $searches = Search::find()
            ->byContentTreeId($this->contentTree->id)
            ->byLanguage($this->from)
            ->asArray()
            ->all();

        if (!$searches) {
            return;
        }

        $searchData = [];
        foreach ($searches as $search) {
            unset($search['id']);
            $search['language'] = $this->to;
            $searchData [] = $search;
        }

        \Yii::$app->db->createCommand()->batchInsert(Search::tableName(), array_keys($search), $searchData)->execute();
    }


    public function copyBaseTranslation()
    {
        $baseTranslation = $this->getBaseTranslateModel();
        unset($baseTranslation['id']);
        $baseTranslation['language'] = $this->to;
        $attributes = \Yii::$app->contentTree->getSearchableAttributes($this->getBaseClassName()::getFormattedTableName());

        $pattern = '/{{%STORAGE_URL_PLACEHOLDER%}}' . $this->from . '(\/([^"])+)+/';
        /**
         * Replace languages in image urls in multi line text.
         */
        foreach ($attributes as $attribute) {
            $text = $baseTranslation[$attribute];
            $result = preg_match_all($pattern, $text, $matches);
            if ($result) {
                $fullMatches = $matches[0];
                foreach ($fullMatches as $match) {
                    /** @var  $filePath
                     *  Replace  STORAGE_URL_PLACEHOLDER with real path;
                     */
                    $filePath = str_replace('{{%STORAGE_URL_PLACEHOLDER%}}', \Yii::getAlias(FileManagerItem::STORAGE_PATH), urldecode($match));
                    if (file_exists($filePath)) {
                        /** @var  $newFile
                         *  Replace language in path
                         */
                        $newFile = str_replace($this->from, $this->to, $filePath);
                        FileHelper::createDirectory(preg_replace('/\/[^\/]*$/', '', $newFile));
                        if (copy($filePath, $newFile)) {
                            $baseTranslation[$attribute] = str_replace($match, str_replace($this->from, $this->to, $match), $baseTranslation[$attribute]);
                        }
                    }
                }
            }
        }

        if (!$baseTranslation) {
            return;
        }

        \Yii::$app->db->createCommand()->insert($this->getTranslateClassName()::tableName(), $baseTranslation)->execute();
    }

    public function copyContentTreeTranslation()
    {
        $this->contentTreeTranslationFrom = $this->contentTree
            ->getTranslation()
            ->andWhere(['language' => $this->from])
            ->one();

        $contentTreeTranslation = ArrayHelper::toArray($this->contentTreeTranslationFrom);

        unset($contentTreeTranslation['id']);
        $contentTreeTranslation['language'] = $this->to;
        $contentTreeTranslation['alias_path'] = $this->getAliasPath();
        if (!$contentTreeTranslation) {
            return;
        }

        \Yii::$app->db->createCommand()->insert(ContentTreeTranslation::tableName(), $contentTreeTranslation)->execute();
    }

    public function copyFileManagerItems()
    {
        $baseModelId = $this->getBaseModel()->id;
        $tableName = $this->getBaseClassName()::getFormattedTableName();
        $newAliasPath = $this->getAliasPath();
        /** copy filemanager items */
        $fileManagerItems = FileManagerItem::find()
            ->byRecordId($baseModelId)
            ->byLanguage($this->from)
            ->byTable($tableName)
            ->asArray()
            ->all();

        $fileManagerData = [];
        $oldPath = '';
        $newPath = '';
        if ($fileManagerItems) {
            $copyToDir = \Yii::getAlias(FileManagerItem::STORAGE_PATH . "$this->to/$newAliasPath");
            FileHelper::createDirectory($copyToDir, 0775, true);
        }
        foreach ($fileManagerItems as $fileManagerItem) {
            unset($fileManagerItem['id']);
            $oldPath = $fileManagerItem['path'];
            $fileName = preg_replace('/^.*\/\s*/', '', $oldPath);
            $fileManagerItem['record_id'] = $baseModelId;
            $fileManagerItem['language'] = $this->to;
            $fileManagerItem['path'] = "$this->to/$newAliasPath/$fileName";
            $fileManagerItem = $this->modifyBlameData($fileManagerItem);
            $fileManagerData[] = $fileManagerItem;
            $copyFromFile = \Yii::getAlias(FileManagerItem::STORAGE_PATH . $oldPath);
            $copyToFile = $copyToDir . '/' . $fileName;
            if (file_exists($copyFromFile) && copy($copyFromFile, $copyToFile)) {
                \Yii::$app->db->createCommand()->batchInsert(FileManagerItem::tableName(), array_keys($fileManagerItem),
                    [$fileManagerItem])->execute();
            }
        }
        return true;
    }


    public function getAliasPath()
    {
        if ($this->newAliasPath) {
            return $this->newAliasPath;
        }
        if (!$this->contentTreeTranslationFrom) {
            throw new Exception('First Copy ContentTreeTranslation For generate alias_path');
        }
        $language = $this->to;
        $masterLanguage = \Yii::$app->websiteMasterLanguage;
        $contentTree = $this->contentTree;
        /** @var ContentTree $parentContentTree */
        $parentContentTree = $contentTree->getParent();
        $aliasPath = $alias = $this->contentTreeTranslationFrom->alias;
        if ($parentContentTree && $contentTree->depth > 1) {
            /** @var ContentTreeTranslation $parentContentTreeTranslation */
            $parentContentTreeTranslation = $parentContentTree->getTranslation()->andWhere(['language' => $language])->one();
            if ($parentContentTreeTranslation) {
                return $this->ensureAliasPathUnique($parentContentTreeTranslation->alias_path . '/' . $alias);
            }
            $aliasPath = '';
            $parentContentTrees = $contentTree
                ->parents()
                ->joinWith('translations')
                ->andWhere(['>', \intermundia\yiicms\models\ContentTree::tableName() . '.depth', 0])
                ->asArray()
                ->all();

            foreach ($parentContentTrees as $contentTree) {
                $translations = ArrayHelper::index($contentTree['translations'], 'language');
                $parentTranslationAlias = isset($translations[$language]) ? $translations[$language]['alias'] : $translations[$masterLanguage]['alias'];
                $aliasPath .= "$parentTranslationAlias/";
            }

            return $this->ensureAliasPathUnique($aliasPath . $alias);
        }

        return $this->ensureAliasPathUnique($alias);
    }

    public function ensureAliasPathUnique($aliasPath)
    {
        $ct = ContentTreeTranslation::find()
            ->byAliasPath($aliasPath)
            ->byLanguage($this->to)
            ->innerJoinWith('contentTree')
            ->andWhere([\intermundia\yiicms\models\ContentTree::tableName() . '.deleted_at' => null])
            ->count();


        if ($ct == 0) {
            $this->newAliasPath = $aliasPath;
            return $this->newAliasPath;
        }

        $numericAliasPath = array_map(function ($contentTreeTranslation) {
            $explode = explode('-', $contentTreeTranslation['alias_path']);
            $lastElement = end($explode);
            return is_numeric($lastElement) ? intval($lastElement) : 0;
        }, ContentTreeTranslation::find()->select('alias_path')->startWith($aliasPath)->asArray()->all());
        $numeric = max($numericAliasPath) + 1;
        $this->owner->alias = $this->owner->alias . '-' . $numeric;

        $this->newAliasPath = $aliasPath . '-' . $numeric;
        return $this->newAliasPath;
    }

    protected function modifyBlameData($data)
    {
        $modifiedData = $data;
        if (isset($modifiedData['created_at'])) {
            $modifiedData['created_at'] = time();
        }
        if (isset($modifiedData['created_by'])) {
            $modifiedData['created_by'] = \Yii::$app->user->id;
        }
        if (isset($modifiedData['updated_at'])) {
            $modifiedData['updated_at'] = time();
        }
        if (isset($modifiedData['updated_by'])) {
            $modifiedData['updated_by'] = \Yii::$app->user->id;
        }
        if (isset($modifiedData['deleted_at']) && $modifiedData['deleted_at']) {
            $modifiedData['updated_at'] = time();
            $modifiedData['deleted_by'] = Yii::$app->user->id;
        }

        return $modifiedData;

    }


}
