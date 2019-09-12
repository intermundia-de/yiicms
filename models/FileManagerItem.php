<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\base\Exception;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "{{%file_manager_item}}".
 *
 * @property int $id
 * @property string $table_name
 * @property string $column_name
 * @property int $record_id
 * @property string $base_url
 * @property string $path
 * @property string $type
 * @property string $mime
 * @property string $language
 * @property int $size
 * @property string $name
 * @property string $oldPath
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $position
 *
 * @property FileManagerItem[] $images
 */
class FileManagerItem extends \yii\db\ActiveRecord
{
    const STORAGE_ALIAS = '@storageUrl/source/';
    const STORAGE_PATH = '@storage/web/source/';
    public $oldPath;

    public function behaviors()
    {
        return [
            BlameableBehavior::class,
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%file_manager_item}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['record_id', 'size', 'created_at', 'updated_at', 'created_by', 'updated_by', 'position'], 'integer'],
            [['base_url', 'mime', 'language'], 'string'],
            [['oldPath'], 'string'],
            [['table_name', 'column_name', 'name'], 'string', 'max' => 255],
            [['path'], 'string', 'max' => 2000],
            [['type'], 'string', 'max' => 55],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'table_name' => Yii::t('intermundiacms', 'Table Name'),
            'column_name' => Yii::t('intermundiacms', 'Column Name'),
            'record_id' => Yii::t('intermundiacms', 'Record ID'),
            'base_url' => Yii::t('intermundiacms', 'Base Url'),
            'path' => Yii::t('intermundiacms', 'Path'),
            'type' => Yii::t('intermundiacms', 'Type'),
            'size' => Yii::t('intermundiacms', 'Size'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
            'created_by' => Yii::t('intermundiacms', 'Created By'),
            'updated_by' => Yii::t('intermundiacms', 'Updated By'),
            'position' => Yii::t('intermundiacms', 'Position'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\FileManagerItemQuery the active query used by this AR class.
     */

    public function beforeSave($insert)
    {
        $fileManagerItems = FileManagerItem::find()->byTable($this->table_name)->byRecordId($this->record_id)->all();

        echo "<pre>";
        var_dump($fileManagerItems);
        echo "</pre>";
        exit;
        return parent::beforeSave($insert);
    }

    public static function find()
    {
        return new \intermundia\yiicms\models\query\FileManagerItemQuery(get_called_class());
    }

    public function getUrl()
    {
        if ($this->path) {
            return Yii::getAlias(self::STORAGE_ALIAS) . str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $this->path);
        }
        return '';
    }

    public function isImage()
    {
        return strpos($this->type, 'image/') === 0;
    }

    public function isVideo()
    {
        return strpos($this->type, 'video/') === 0;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Yii::getAlias('@storage/web/source/') . $this->path;
    }

    /**
     * @return string
     */
    public function getRelativePath()
    {
        return 'source/' . $this->path;
    }

    /**
     * @return string
     */
    public function getFormattedSize()
    {
        return Yii::$app->formatter->asShortSize($this->size, 0);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages()
    {
        return $this->hasMany(FileManagerItem::class, ['record_id' => 'id', 'column_name' => 'column_name'])->andWhere(['table_name' => ContentTree::TABLE_NAME_FILE_MANAGER_ITEM]);
    }

    /**
     *
     *
     * @param $path
     * @throws \yii\base\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function makeDir($path)
    {
        $path = str_replace('/' . $this->getFileName(), '', $path);
        if (!file_exists($path)) {
            FileHelper::createDirectory($path);
        }
    }

    /**
     *
     *
     * @throws \yii\base\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function renameFileStorageItem()
    {
        $oldFileName = Yii::getAlias(self::STORAGE_PATH . $this->oldPath);
        if (file_exists($oldFileName)) {
            $newFileName = Yii::getAlias(self::STORAGE_PATH . $this->path);
            if ($oldFileName != $newFileName) {
                $this->makeDir($newFileName);
                if (!rename($oldFileName, $newFileName)) {
                    throw new Exception('Could Not Rename File');
                }
            }
        }
    }

    /**
     *
     *
     * @throws \yii\base\Exception
     * @author Mirian Jintchvelashvili <mirianjinchvelashvili@gmail.com>
     */
    public function copyFileStorageItem()
    {
        $oldFileName = Yii::getAlias(self::STORAGE_PATH . $this->oldPath);
        if (file_exists($oldFileName)) {
            $newFileName = Yii::getAlias(self::STORAGE_PATH . $this->path);
            if ($oldFileName != $newFileName) {
                $this->makeDir($newFileName);
                if (!copy($oldFileName, $newFileName)) {
                    throw new Exception('Could Not Copy File.');
                }
            }
        } else {
            throw new Exception('File Does Not Exist.', 404);
        }
    }

    /**
     *
     *
     * @param $language
     * @param $alias_path
     * @throws Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function updatePath($language, $alias_path)
    {
        $this->oldPath = $this->path;
        $this->path = $language . '/' . $alias_path . '/' . $this->getFileName();
        if ($this->save()) {
            $this->renameFileStorageItem();
        }

        $pdfItems = FileManagerItem::find()
            ->byTable('file_manager_item')
            ->byRecordId($this->id)
            ->all();

        foreach ($pdfItems as $pdfItem) {
            $oldPath = Yii::getAlias(self::STORAGE_PATH . $pdfItem->path);
            $newFileName = rtrim($this->path, '.pdf');
            $newFileDirName = Yii::getAlias(self::STORAGE_PATH . $newFileName);

            $pdfItem->path = $newFileName . '/' . $pdfItem->name;
            if ($pdfItem->save()) {
                if (!file_exists($newFileDirName)) {
                    FileHelper::createDirectory($newFileDirName);
                }
                if (!rename($oldPath, $newFileDirName . '/' . $pdfItem->name)) {
                    throw new Exception('Could Not Rename Pdf File');
                }
            }
        }
    }

    public function getFileName()
    {
        return $this->column_name . '_' . $this->name;
    }

}
