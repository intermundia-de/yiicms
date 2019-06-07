<?php
/**
 * User: zura
 * Date: 6/23/18
 * Time: 11:27 AM
 */

namespace intermundia\yiicms\behaviors;

use intermundia\yiicms\models\BaseTranslateModel;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\FileManagerItem;
use intermundia\yiicms\web\Application;
use intermundia\yiicms\web\BackendApplication;
use intermundia\yiicms\web\BaseApplication;
use function GuzzleHttp\Psr7\parse_request;
use Imagick;
use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\web\UploadedFile;


/**
 * Class FileManagerItemBehavior
 *
 * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\behaviors
 */
class FileManagerItemBehavior extends Behavior
{
    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var BaseTranslateModel
     */
    public $owner;

    const EVENT_AFTER_FILE_SAVE = 'afterFileSave';
    const PDF_MIME_TYPE = 'application/pdf';

    public $tableName = null;

    public $columnNames = [];

    public $deletedImages;

    public $deleteColumnNames = [];

    public $recordIdAttribute = 'id';

    public $urlPrefix = '@storageUrl/source/';

    public $storagePath = '@storage/web/source/';

    public $filePath = '[[attribute_language]]/[[attribute_alias_path]]/[[column]]_[[filename]].[[extension]]';

    public $extractImagesFromPdf = false;

    private static $fileManagerItems = null;

    /**
     * FileManagerItemBehavior constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (Yii::$app instanceof Application){
            $this->setFileManagerItems();
        }
    }

    
    protected function setFileManagerItems(){
        if (self::$fileManagerItems === null) {
            if (Yii::$app->pageContentTree) {
                $items = array_merge([Yii::$app->websiteContentTree],
                    Yii::$app->pageContentTree->children()->notDeleted()->notHidden()->asArray()->all(),
                    [ArrayHelper::toArray(Yii::$app->pageContentTree)]);
            } else {
                $items = ContentTree::find()->notDeleted()->asArray()->all();
            }
            $currentLanguageFileManagerItems = ArrayHelper::index(
                FileManagerItem::find()
                    ->andWhere(
                        [
                            'in',
                            ['record_id', 'table_name'],
                            array_map(function ($item) {
                                return ['record_id' => $item['record_id'], 'table_name' => $item['table_name']];
                            }, $items)
                        ]
                    )
                    ->byLanguage(Yii::$app->language)
                    ->all(),
                null, [
                'table_name',
                'record_id',
                'column_name'
            ]);
            if (Yii::$app instanceof BaseApplication) {
                $websiteMasterLanguageFileManagerItems = ArrayHelper::index(
                    FileManagerItem::find()
                        ->andWhere(
                            [
                                'in',
                                ['record_id', 'table_name'],
                                array_map(function ($item) {
                                    return ['record_id' => $item['record_id'], 'table_name' => $item['table_name']];
                                }, $items)
                            ]
                        )
                        ->byLanguage(Yii::$app->websiteMasterLanguage)
                        ->all(),
                    null, [
                    'table_name',
                    'record_id',
                    'column_name'
                ]);
                foreach ($websiteMasterLanguageFileManagerItems as $tableName => $files) {
                    foreach ($files as $recordId => $innerFiles) {
                        $currentLanguageFileManagerItems[$tableName][$recordId] = array_merge($websiteMasterLanguageFileManagerItems[$tableName][$recordId],
                            ArrayHelper::getValue($currentLanguageFileManagerItems, $tableName . '.' . $recordId, []));
                    }
                }
            }
            self::$fileManagerItems = $currentLanguageFileManagerItems;

        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
//            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_AFTER_FIND => 'afterFind',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
//            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function afterFind()
    {
        $this->populateFields();
        /** @var FileManagerItem[] $fileManagerItems */
        $fileManagerItemsByColumn = $this->findGroupedItems();
        foreach ($this->columnNames as $columnKey => $columnName) {
            $this->owner->{$columnKey} = ArrayHelper::getValue($fileManagerItemsByColumn, $columnName, []);
        }
    }

    public function beforeValidate()
    {
        foreach ($this->columnNames as $columnKey => $columnName) {
            $this->owner->{$columnName} = UploadedFile::getInstances($this->owner, $columnName);

            $deleted = $this->owner->{$columnName . '_deleted'};
            if ($deleted && is_string($deleted)) {
                $this->owner->{$columnName . '_deleted'} = array_map('intval',
                    explode(',', $deleted));
            } else {
                $this->owner->{$columnName . '_deleted'} = [];
            }

            continue;
        }
    }

    /**
     * After save event.
     * @throws \yii\base\Exception
     * @throws \Throwable
     */
    public function afterSave()
    {
        $this->populateFields();
        foreach ($this->columnNames as $key => $columnName) {

            /** @var UploadedFile[]|FileManagerItem[] $fileAttributes */
            $fileAttributes = $this->owner->{$columnName};
            if (!$fileAttributes) {
                continue;
            }

            $transaction = Yii::$app->db->beginTransaction();
            foreach ($fileAttributes as $fileAttribute) {

                $fileManagerItem = new FileManagerItem();
                $fileManagerItem->base_url = Yii::getAlias($this->urlPrefix);
                $fileManagerItem->table_name = $this->tableName;
                $fileManagerItem->column_name = $columnName;
                $fileManagerItem->language = $this->owner->language;
                $fileManagerItem->record_id = $this->owner->{$this->recordIdAttribute};

                $path = $this->getUploadedFilePath($columnName, $fileAttribute, $fileManagerItem);
                FileHelper::createDirectory(pathinfo($path, PATHINFO_DIRNAME), 0775, true);

                if (!$fileManagerItem->save()) {
                    throw new \yii\db\Exception(FileManagerItem::class . ' was not saved');
                }
                if (is_uploaded_file($fileAttribute->tempName)) {
                    $moveFile = $fileAttribute->saveAs($path);
                } else {
                    $moveFile = copy($fileAttribute->tempName, $path);
                }

                if (!$moveFile) {
                    $transaction->rollBack();
                    throw new Exception("Error in saving file on file system");
                }
            }

            $transaction->commit();
            $this->owner->trigger(static::EVENT_AFTER_FILE_SAVE);
        }
        $this->deleteFileManagerItems();
    }

    /**
     * Returns file path for attribute.
     *
     * @param $columnName
     * @param $uploadedFile
     * @param FileManagerItem $fileManagerItem
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getUploadedFilePath($columnName, UploadedFile $uploadedFile, FileManagerItem $fileManagerItem)
    {
        $path = $this->filePath;
        $path = Yii::getAlias($path);

        $pi = pathinfo($uploadedFile->name);
        $fileName = ArrayHelper::getValue($pi, 'filename');
        $extension = strtolower(ArrayHelper::getValue($pi, 'extension'));

        $path = preg_replace_callback('|\[\[([\w\_/]+)\]\]|',
            function ($matches) use ($columnName, $fileName, $extension) {
                $name = $matches[1];
                switch ($name) {
                    case 'extension':
                        return $extension;
                    case 'filename':
                        return $fileName;
                    case 'column':
                        return $columnName;
                    case 'basename':
                        return implode('.', array_filter([$fileName, $extension]));
//                case 'app_root':
//                    return Yii::getAlias('@app');
//                case 'web_root':
//                    return Yii::getAlias('@webroot');
//                case 'base_url':
//                    return Yii::getAlias('@web');
//                case 'model':
//                    $r = new \ReflectionClass($this->owner->className());
//                    return lcfirst($r->getShortName());
//                case 'column':
//                    return lcfirst($this->attribute);
//                case 'id':
//                case 'pk':
//                    $pk = implode('_', $this->owner->getPrimaryKey(true));
//                    return lcfirst($pk);
//                case 'id_path':
//                    return static::makeIdPath($this->owner->getPrimaryKey());
//                case 'parent_id':
//                    return $this->owner->{$this->parentRelationAttribute};
                }
                if (preg_match('|^attribute_(\w+)$|', $name, $am)) {
                    $attribute = $am[1];
                    return $this->owner->{$attribute};
                }
//            if (preg_match('|^md5_attribute_(\w+)$|', $name, $am)) {
//                $attribute = $am[1];
//                return md5($this->owner->{$attribute});
//            }
                return '[[' . $name . ']]';
            }, $path);

        $fileManagerItem->path = $path;
        $fileManagerItem->type = FileHelper::getMimeType($uploadedFile->tempName);
        $fileManagerItem->size = $uploadedFile->size;
        $fileManagerItem->name = $uploadedFile->name;

        return $this->resolvePath($path);
    }

    /**
     * Replaces all placeholders in path variable with corresponding values
     *
     * @param $path
     * @return string
     */
    public function resolvePath($path)
    {
        return Yii::getAlias($this->storagePath . $path);
    }


    public function deleteFileManagerItems()
    {
        $toDeleteIds = [];
        foreach ($this->columnNames as $columnName) {
            $toDeleteIds = ArrayHelper::merge($toDeleteIds, $this->owner->{$columnName . '_deleted'});
        }
        FileManagerItem::deleteAll(['id' => $toDeleteIds]);
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return array|FileManagerItem[]
     */
    protected function findGroupedItems()
    {
        $indexed = ArrayHelper::getValue(self::$fileManagerItems,
            "$this->tableName.{$this->owner->{$this->recordIdAttribute}}");
        
        if (!self::$fileManagerItems || !$indexed) {
            $fileManagerItems = FileManagerItem::find()
                ->byTable($this->tableName)
                ->byRecordId($this->owner->{$this->recordIdAttribute})
                ->byColumns($this->columnNames)
                ->byLanguage($this->owner->language)
                ->all();
            $indexed = ArrayHelper::index($fileManagerItems, null, 'column_name');
        }

        return $indexed;
    }

    public function populateFields()
    {
        $this->tableName = $this->owner->getModelClass()::getFormattedTableName();
        $this->recordIdAttribute = $this->owner->getForeignKeyNameOnModel();
    }
}
