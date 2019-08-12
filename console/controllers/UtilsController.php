<?php
/**
 * User: zura
 * Date: 2/28/19
 * Time: 5:52 PM
 */

namespace intermundia\yiicms\console\controllers;


use console\controllers\AppController;
use intermundia\yiicms\models\BaseModel;
use intermundia\yiicms\models\BaseTranslateModel;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeMenu;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\ContinentTranslation;
use intermundia\yiicms\models\CountryTranslation;
use intermundia\yiicms\models\FileManagerItem;
use intermundia\yiicms\models\Language;
use intermundia\yiicms\models\query\BaseQuery;
use intermundia\yiicms\models\query\BaseTranslationQuery;
use intermundia\yiicms\models\query\ContentTreeTranslationQuery;
use intermundia\yiicms\models\Search;
use intermundia\yiicms\models\TimelineEvent;
use intermundia\yiicms\models\User;
use intermundia\yiicms\models\UserProfile;
use intermundia\yiicms\models\WidgetText;
use intermundia\yiicms\models\WidgetTextTranslation;
use PDO;
use PDOException;
use phpDocumentor\Reflection\Location;
use Yii;
use yii\console\Controller;
use yii\db\ActiveQuery;
use yii\db\conditions\AndCondition;
use yii\db\conditions\OrCondition;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;

/**
 * Class UtilsController
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\console\controllers
 */
class UtilsController extends Controller
{

    public function actionSwitchLanguage($from, $to)
    {
        Yii::$app->db->createCommand('set foreign_key_checks=0')->execute();

        $transaction = Yii::$app->db->beginTransaction();

        $STORAGE_URL = '{{%STORAGE_URL_PLACEHOLDER%}}';
        // Update in base model translations

//        Console::output("Copy the following SQL UPDATE statements and run in your database console");
//        Console::output("=========================================================================");
        foreach (Yii::$app->contentTree->editableContent as $tableName => $item) {
            $baseModelClass = $item['class'];
            $translateModelClass = $baseModelClass::getTranslateModelClass();
            $translateModelClass::updateAll(['language' => $to], ['language' => $from]);


            // Update base model translation attributes
            $attributes = ArrayHelper::getValue($item, 'searchableAttributes');
            if (!empty($attributes)) {
                $updateAttributesText = [];
                foreach ($attributes as $attribute) {
                    $updateAttributesText[] = "$attribute = REPLACE($attribute, '{$STORAGE_URL}{$from}/', '{$STORAGE_URL}$to/')";
                }

                $translateTableName = preg_replace('/^(\{\{%)|(}}$)/', '', $translateModelClass::tableName());
                $sql = "UPDATE " . $translateTableName . " SET " . implode(", ", $updateAttributesText) . "
                        WHERE language = '$to';";
                if (( $count = $this->executePdo($sql) )) {
                    Console::output("Run command: \"$sql\"");
                    Console::output("Affected: \"$count\" rows");
                }
//                Console::output($sql);
            }
        }
        Console::output("=========================================================================");

        //Update other translatable tables
        ContentTreeTranslation::updateAll(['language' => $to], ['language' => $from]);
        WidgetTextTranslation::updateAll(['language' => $to], ['language' => $from]);
        FileManagerItem::updateAll(['language' => $to], ['language' => $from]);
        UserProfile::updateAll(['locale' => $to], ['locale' => $from]);
        CountryTranslation::updateAll(['language' => $to], ['language' => $from]);
        ContinentTranslation::updateAll(['language' => $to], ['language' => $from]);
        Search::updateAll(['language' => $to], ['language' => $from]);
        Language::updateAll(['code' => $to], ['code' => $from]);

        // Update file manager item records
        $fileManagerItems = FileManagerItem::find()->all();
        foreach ($fileManagerItems as $fileManagerItem) {
            if (strpos($fileManagerItem->path, "$from/") === 0) {
//                Console::output('Changing file manager item path');
//                Console::output("Old path: $fileManagerItem->path");
                $fileManagerItem->path = preg_replace("/^$from/", $to, $fileManagerItem->path);
//                Console::output("New path: $fileManagerItem->path");
                if (!$fileManagerItem->save()) {
                    $transaction->rollBack();
                    Console::output('Error while changing path of file manager item by id: ' . $fileManagerItem->id);
                }
            }
        }

        $transaction->commit();

        // Rename folder
        Console::output('Finished updating database');
        if (file_exists(Yii::getAlias("@storage/web/source/$from"))) {
            if (rename(Yii::getAlias("@storage/web/source/$from"), Yii::getAlias("@storage/web/source/$to"))) {
                Console::output('Renaming storage folder successful');
            } else {
                Console::output('Error occured while renaming storage folder');
            }
        }

        Yii::$app->db->createCommand('set foreign_key_checks=1')->execute();
        Console::output('Foreign key checks set to 1');
    }

    public function actionCopyLanguage($fromWebsiteKey, $toWebsiteKey, $from, $to)
    {
        $connection = Yii::$app->db;
        $user = User::findOne(1);
        Yii::$app->user->setIdentity($user);
        Yii::$app->websiteContentTree = ContentTree::findClean()->byKey($fromWebsiteKey)->one();
        $websiteContentTree = ContentTree::findClean()->byKey($toWebsiteKey)->byTableName('website')->one();

        Yii::$app->db->createCommand()->update('{{%content_tree}}', [
            'lft' => Yii::$app->websiteContentTree->lft,
            'rgt' => Yii::$app->websiteContentTree->rgt,
        ], ['id' => $websiteContentTree->id])->execute();
        if (!$websiteContentTree) {
            Console::output('Please insert website first');

            return;
        }
        Yii::$app->db->createCommand('set foreign_key_checks=0')->execute();

        $transaction = Yii::$app->db->beginTransaction();
        $storagePath = Yii::getAlias('@storage/web/source/');

        // Update in base model translations
        Console::output("Copying has started");
        $STORAGE_URL = '{{%STORAGE_URL_PLACEHOLDER%}}';
//        Console::output("Copy the following SQL UPDATE statements and run in your database console");
//        Console::output("=========================================================================");
        foreach (Yii::$app->contentTree->editableContent as $tableName => $item) {
            if ($tableName == 'website') {
                continue;
            }
            $attributes = ArrayHelper::getValue($item, 'searchableAttributes');
            /** @var BaseModel $baseModelClass */
            $baseModelClass = $item['class'];
            /** @var BaseTranslateModel $translateModelClass */
            $translateModelClass = $baseModelClass::getTranslateModelClass();
            /** @var BaseQuery $baseModelQuery */
            $baseModelQuery = $baseModelClass::find();
            $baseModels = $baseModelQuery
                ->innerJoin($translateModelClass::tableName() . ' t',
                    't.' . $translateModelClass::getForeignKeyNameOnModel() . " = $tableName.id AND t.language = :lang",
                    [
                        'lang' => $from
                    ])
                ->with([
                    'translation' => function ($query) use ($from) {
                        /** @var BaseTranslationQuery $query */
                        return $query->byLanguage($from);
                    }
                ])
                ->all();

            foreach ($baseModels as $baseModel) {
                $translationModelTableName = $baseModel::getTranslateModelClass()::tableName();
                /** @var BaseModel $baseModel */
                $data = $baseModel->toArray();
                /** @var BaseModel $newBaseModel */
                unset($data['id']);
                $data = $this->modifyBlameData($data);
                /** copying into baseModel */
                $connection->createCommand()->insert($tableName, $data)->execute();
                $baseModelId = $connection->getLastInsertID();

                /** @var copying into baseModelTranslation */
                $foreignKey = $translateModelClass::getForeignKeyNameOnModel();
                $translationData = $connection->createCommand("SELECT * FROM {$translationModelTableName} WHERE language = :lang AND {$foreignKey} = :baseId")
                    ->bindValue(':lang', $from)
                    ->bindValue(':baseId', $baseModel->id)
                    ->queryOne();


                unset($translationData['id']);
                $translationData[$translateModelClass::getForeignKeyNameOnModel()] = $baseModelId;
                $translationData['language'] = $to;
                $translationData = $this->modifyBlameData($translationData);

                $connection->createCommand()->insert($translationModelTableName, $translationData)->execute();


                /**start copying into content Tree */
                $contentTreeData = ContentTree::findClean()
                    ->byRecordIdTableName($baseModel->id, $tableName)
                    ->asArray()
                    ->one();
                if ($contentTreeData) {

                    $contentTreeTranslationData = ContentTreeTranslation::find()->byLanguageAndTreeId($contentTreeData['id'],
                        $from)->asArray()->one();

                    /** linked items */
                    $linkedContentTreeData = ContentTree::findClean()
                        ->byLinkId($contentTreeData['id'])
                        ->asArray()
                        ->all();


                    $fromContentTreeId = $contentTreeData['id'];
                    unset($contentTreeData['id']);
                    $contentTreeData['record_id'] = $baseModelId;
                    $contentTreeData['website'] = $websiteContentTree->id;
                    $contentTreeData = $this->modifyBlameData($contentTreeData);
                    $connection->createCommand()->insert(ContentTree::tableName(), $contentTreeData)->execute();


                    $newContentTreeId = $connection->getLastInsertID();
                    unset($contentTreeTranslationData['id']);
                    $contentTreeTranslationData['content_tree_id'] = $newContentTreeId;
                    $contentTreeTranslationData['language'] = $to;

                    $connection->createCommand()->insert(ContentTreeTranslation::tableName(),
                        $contentTreeTranslationData)->execute();

                    /** copy all linked items */
                    if ($linkedContentTreeData) {
                        foreach ($linkedContentTreeData as $linkedContentTree) {
                            $linkedContentTreeTranslationData = ContentTreeTranslation::find()->byLanguageAndTreeId($linkedContentTree['id'],
                                $from)->asArray()->one();
                            unset($linkedContentTree['id']);
                            $linkedContentTree['record_id'] = $baseModelId;
                            $linkedContentTree['link_id'] = $newContentTreeId;
                            $linkedContentTree['website'] = $websiteContentTree->id;
                            $linkedContentTree = $this->modifyBlameData($linkedContentTree);

                            $connection->createCommand()->insert(ContentTree::tableName(),
                                $linkedContentTree)->execute();

                            $newLinkedContentTreeId = $connection->getLastInsertID();
                            unset($linkedContentTreeTranslationData['id']);
                            $linkedContentTreeTranslationData['content_tree_id'] = $newLinkedContentTreeId;
                            $linkedContentTreeTranslationData['language'] = $to;

                            $connection->createCommand()->insert(ContentTreeTranslation::tableName(),
                                $linkedContentTreeTranslationData)->execute();

                        }
                    }

                    // Copy search items
                    $searches = Search::find()
                        ->byContentTreeId($fromContentTreeId)
                        ->byLanguage($from)
                        ->asArray()
                        ->all();

                    foreach ($searches as $search) {
                        unset($search['id']);
                        $search['language'] = $to;
                        $search['record_id'] = $baseModelId;
                        $search['content_tree_id'] = $newContentTreeId;
                        $connection->createCommand()->insert(Search::tableName(), $search)->execute();
                    }

                    // Copy menu

                    $menuItems = ContentTreeMenu::find()
                        ->byContentTreeId($fromContentTreeId)
                        ->asArray()
                        ->all();

                    foreach ($menuItems as $menuItem) {
                        unset($menuItem['id']);
                        $menuItem['content_tree_id'] = $newContentTreeId;
                        $connection->createCommand()->insert(ContentTreeMenu::tableName(), $menuItem)->execute();
                    }
                }


                /** copy filemanager items */
                $fileManagerItems = FileManagerItem::find()
                    ->byRecordId($baseModel->id)
                    ->byLanguage($from)
                    ->byTable($tableName)
                    ->asArray()
                    ->all();


                foreach ($fileManagerItems as $fileManagerItem) {
                    unset($fileManagerItem['id']);
                    $fileManagerItem['record_id'] = $baseModelId;
                    $fileManagerItem['language'] = $to;
                    $fileManagerItem['path'] = str_replace($from, $to, $fileManagerItem['path']);
                    $fileManagerItem = $this->modifyBlameData($fileManagerItem);
                    $connection->createCommand()->insert(FileManagerItem::tableName(), $fileManagerItem)->execute();
                }

            }
            if (!empty($attributes)) {
                $updateAttributesText = [];
                foreach ($attributes as $attribute) {
                    $updateAttributesText[] = "$attribute = REPLACE($attribute, '{$STORAGE_URL}{$from}/', '{$STORAGE_URL}$to/')";
                }

                $translateTableName = preg_replace('/^(\{\{%)|(}}$)/', '', $translateModelClass::tableName());
                $sql = "UPDATE " . $translateTableName . " SET " . implode(", ",
                        $updateAttributesText) . " WHERE language = '$to' ;";
                if (( $count = $this->executePdo($sql) )) {
                    Console::output("Run command: \"$sql\"");
                    Console::output("Affected: \"$count\" rows");
                }
            }
        }

        $textWidgets = WidgetTextTranslation::find()
            ->andWhere([WidgetTextTranslation::tableName() . '.language' => $from])
            ->asArray()
            ->all();

        foreach ($textWidgets as $textWidget) {
            unset($textWidget['id']);
            $textWidget['language'] = $to;
            $connection->createCommand()->insert(WidgetTextTranslation::tableName(), $textWidget)->execute();
        }


        $this->copyDirectory($storagePath . '/' . $from, $storagePath . '/' . $to);

        $transaction->commit();

        Console::output("=========================================================================");
        Console::output("Copying has finished");
    }

    public function actionAddLanguage($websiteKey, $from, $to)
    {
        $connection = Yii::$app->db;
        $transaction = Yii::$app->db->beginTransaction();
        $user = User::findOne(1);
        Yii::$app->user->setIdentity($user);
        Yii::$app->websiteContentTree = ContentTree::findClean()->byKey($websiteKey)->one();
        $website = ContentTree::findClean()->byKey($websiteKey)->byTableName('website')->one();
        if (!$website) {
            Console::output('Please insert website first');

            return;
        }
        Yii::$app->db->createCommand('set foreign_key_checks=0')->execute();
        $storagePath = Yii::getAlias('@storage/web/source/');

        $contentTrees = ContentTree::find()
            ->select('id')
            ->andWhere(['<>', 'table_name', 'website'])
            ->asArray()
            ->all();
        $contentTreeTranslations = ContentTreeTranslation::find()
            ->byTreeId(ArrayHelper::getColumn($contentTrees, 'id'))
            ->byLanguage($from)
            ->asArray()
            ->all();
        $ctTranslationData = [];
        foreach ($contentTreeTranslations as $contentTreeTranslation) {
            unset($contentTreeTranslation['id']);
            $contentTreeTranslation['language'] = $to;
            $ctTranslationData[] = $contentTreeTranslation;
        }
        if (!$contentTreeTranslations) {
            throw new Exception('Translation Not Found');
        }
        $connection->createCommand()->batchInsert(ContentTreeTranslation::tableName(),
            array_keys($contentTreeTranslation),
            $ctTranslationData)->execute();

        Console::output("Copying has started");
        $STORAGE_URL = '{{%STORAGE_URL_PLACEHOLDER%}}';
//        Console::output("Copy the following SQL UPDATE statements and run in your database console");
//        Console::output("=========================================================================");
        foreach (Yii::$app->contentTree->editableContent as $tableName => $item) {
            $attributes = ArrayHelper::getValue($item, 'searchableAttributes');
            $baseModelClass = $item['class'];
            /** @var BaseTranslateModel $translateModelClass */
            $translateModelClass = $baseModelClass::getTranslateModelClass();
            $foreignKey = $translateModelClass::getForeignKeyNameOnModel();
            $translationModelTableName = $baseModelClass::getTranslateModelClass()::tablename();
            $baseTranslations = $connection->createCommand("SELECT * FROM {$translationModelTableName} WHERE language = :lang")
                ->bindValue(':lang', $from)
                ->queryAll();

            $data = [];

            foreach ($baseTranslations as $baseTranslation) {
                unset($baseTranslation['id']);
                $baseTranslation['language'] = $to;
                $baseTranslation = $this->modifyBlameData($baseTranslation);
                $data[] = $baseTranslation;
            }

            $connection->createCommand()->batchInsert($translateModelClass::tableName(),
                array_keys($baseTranslation),
                $data)->execute();

            if (!empty($attributes)) {
                $updateAttributesText = [];
                foreach ($attributes as $attribute) {
                    $updateAttributesText[] = "$attribute = REPLACE($attribute, '{$STORAGE_URL}{$from}/', '{$STORAGE_URL}$to/')";
                }

                $translateTableName = preg_replace('/^(\{\{%)|(}}$)/', '', $translateModelClass::tableName());
                $sql = "UPDATE " . $translateTableName . " SET " . implode(", ",
                        $updateAttributesText) . " WHERE language = '$to' ;";
                if (( $count = $this->executePdo($sql) )) {
                    Console::output("Run command: \"$sql\"");
                    Console::output("Affected: \"$count\" rows");
                }
//                Console::output($sql);
            }

        }

        Console::output("=========================================================================");

        $searches = Search::find()
            ->byLanguage($from)
            ->asArray()
            ->all();

        $searchData = [];
        foreach ($searches as $search) {
            unset($search['id']);
            $search['language'] = $to;
            $search = $this->modifyBlameData($search);
            $searchData[] = $search;
        }

        $connection->createCommand()->batchInsert(Search::tableName(),
            array_keys($search),
            $searchData)->execute();


        $textWidgets = WidgetTextTranslation::find()
            ->andWhere([WidgetTextTranslation::tableName() . '.language' => $from])
            ->asArray()
            ->all();

        foreach ($textWidgets as $textWidget) {
            unset($textWidget['id']);
            $textWidget['language'] = $to;
            $connection->createCommand()->insert(WidgetTextTranslation::tableName(), $textWidget)->execute();
        }


        $fileManagerItems = FileManagerItem::find()
            ->byLanguage($from)
            ->asArray()
            ->all();

        foreach ($fileManagerItems as $fileManagerItem) {
            unset($fileManagerItem['id']);
            $fileManagerItem['language'] = $to;
            $fileManagerItem['path'] = str_replace($from, $to, $fileManagerItem['path']);
            $fileManagerItem = $this->modifyBlameData($fileManagerItem);
            $connection->createCommand()->insert(FileManagerItem::tableName(), $fileManagerItem)->execute();
        }

        $this->copyDirectory($storagePath . '/' . $from, $storagePath . '/' . $to);

        $transaction->commit();

        Console::output("=========================================================================");
        Console::output("Copying has finished");


    }

    private function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir) )) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private function modifyBlameData($data)
    {
        $modifiedData = $data;
        if (isset($modifiedData['created_at'])) {
            $modifiedData['created_at'] = time();
        }
        if (isset($modifiedData['created_by'])) {
            $modifiedData['created_by'] = Yii::$app->user->id;
        }
        if (isset($modifiedData['updated_at'])) {
            $modifiedData['updated_at'] = time();
        }
        if (isset($modifiedData['updated_by'])) {
            $modifiedData['updated_by'] = Yii::$app->user->id;
        }
        if (isset($modifiedData['deleted_at']) && $modifiedData['deleted_at']) {
            $modifiedData['updated_at'] = time();
            $modifiedData['deleted_by'] = Yii::$app->user->id;
        }

        return $modifiedData;

    }

    private function removeAttributes(&$attributes)
    {
        $toRemoveAttributes = [
            'id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'deleted_at',
            'deleted_by'
        ];

        foreach ($toRemoveAttributes as $attr) {
            array_splice($attributes, array_search($attr, $attributes), 1);
        }
    }

    public function actionCopyFilemanagerItemsFromWebsiteToWebsite($fromWebsite, $toWebsite, $from, $to)
    {
        $connection = Yii::$app->db;
        $user = User::findOne(1);
        Yii::$app->user->setIdentity($user);
        $transaction = Yii::$app->db->beginTransaction();
        $storagePath = Yii::getAlias('@storage/web/source/');
        // Update in base model translations
        Console::output("Copying has started");
        FileManagerItem::deleteAll(['language' => $to]);
        if (strpos($fromWebsite, '.en') !== false) {
            $fromWebsite = 'website';
        }

        /** @var ContentTreeTranslation[] $fromTranslations */
        $fromTranslations = ArrayHelper::index(ContentTreeTranslation::find()
            ->byLanguage($from)
            ->andWhere(['not', ['alias_path' => $fromWebsite]])
            ->asArray()
            ->all(),
            'alias_path');
        $toTranslations = ArrayHelper::index(
            ContentTreeTranslation::find()
                ->asArray()
                ->andWhere(['not', ['alias_path' => $toWebsite]])
                ->byLanguage($to)
                ->all(),
            'alias_path');


        foreach ($fromTranslations as $alias_path => $fromTranslation) {
            if (isset($toTranslations[$alias_path])) {
                $toTranslation = $toTranslations[$alias_path];
                $toTranslationContentTree = ContentTree::findClean()
                    ->byId($toTranslation['content_tree_id'])
                    ->linkedIdIsNull()
                    ->one();
                $fromTranslationContentTree = ContentTree::findClean()
                    ->byId($fromTranslation['content_tree_id'])
                    ->linkedIdIsNull()
                    ->one();
                if (!$fromTranslationContentTree) {
                    continue;
                }
                $fileManagerItems = FileManagerItem::find()
                    ->byRecordId($fromTranslationContentTree->record_id)
                    ->byTable($fromTranslationContentTree->table_name)
                    ->byLanguage($from)
                    ->asArray()
                    ->all();
                $fileManagerData = [];
                foreach ($fileManagerItems as $fileManagerItem) {
                    unset($fileManagerItem['id']);
                    $fileManagerItem['language'] = $to;
                    $fileManagerItem['record_id'] = $toTranslationContentTree->record_id;
                    $pathes = explode('/', $fileManagerItem['path']);
                    $pathes[0] = $to;
                    $fileManagerItem['path'] = implode('/', $pathes);
                    $fileManagerData[] = $this->modifyBlameData($fileManagerItem);
                }

                if ($fileManagerData) {
                    $connection->createCommand()
                        ->batchInsert(FileManagerItem::tableName(), array_keys($fileManagerItem), $fileManagerData)
                        ->execute();
                }
            }
        }

        FileHelper::removeDirectory($storagePath . $to);
        FileHelper::createDirectory($storagePath . $to);
        $this->copyDirectory($storagePath . $from, $storagePath . $to);
        $transaction->commit();

        Console::output("=========================================================================");
        Console::output("Copying has finished");
    }


    private static function removeEmptyDirectories($dir)
    {
        $filesLeft = false;
        if (!is_dir($dir)) {
            return;
        }
        if (!is_link($dir)) {
            if (!( $handle = opendir($dir) )) {
                return;
            }
            while (( $file = readdir($handle) ) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . DIRECTORY_SEPARATOR . $file;
                if (is_dir($path)) {
                    static::removeEmptyDirectories($path);
                }
                $filesLeft = true;
            }
            closedir($handle);
        }
        if (!$filesLeft) {
            if (is_link($dir)) {
                static::unlink($dir);
            } else {
                rmdir($dir);
            }
        }
    }

    private function updateDirectory($contentTreeTranslation)
    {
        $oldAliasPath = $contentTreeTranslation->getCorrectFileManagerPath();
        if (!$oldAliasPath) {
            return;
        }
        $oldDirectoryPath = $contentTreeTranslation->getFileManagerDirectoryPath($oldAliasPath);

        //Create new folder if needed, keep old directory
        if ($oldDirectoryPath && file_exists($oldDirectoryPath)) {
            $newDirectoryPath = $this->getFileManagerDirectoryPath();
            \yii\helpers\FileHelper::createDirectory($newDirectoryPath, 0775, true);
            if ($oldDirectoryPath != $newDirectoryPath) {
                try {
                    \yii\helpers\FileHelper::copyDirectory($oldDirectoryPath, $newDirectoryPath);
                } catch (\Exception $e) {
                    throw new Exception('Could Not Rename File While updating contentTreeTranslation language:' . $this->language);
                }
            }
        }
    }

    public function actionFixAliasAndFileManagerItems($websiteKey)
    {
        $connection = Yii::$app->db;
        Yii::$app->websiteContentTree = ContentTree::findClean()->byKey($websiteKey)->one();
        if (!Yii::$app->websiteContentTree) {
            Console::error("Website content tree was not found for {$websiteKey}");

            return;
        }
        Yii::$app->websiteMasterLanguage = \Yii::$app->multiSiteCore->websites[$websiteKey]['masterLanguage'];
        $contentTreeItems = ContentTree::find()
            ->orderBy(['lft' => SORT_ASC])
            ->joinWith('translations')
            ->notDeleted()
            ->andWhere(['<>', 'table_name', 'website'])
            ->all();

        $failedFmiItemIds = [];
        $notFoundFmiItemIds = [];

        foreach ($contentTreeItems as $contentTreeItem) {
            foreach ($contentTreeItem->translations as $contentTreeTranslation) {

                /*
                 * Set selfUpdate Only tot true, to update alias and alias path using SluggableBehavior
                 * Children items won't be updated.
                 */
                $contentTreeTranslation->selfUpdateOnly = true;

                $beforeUpdateAlias = $contentTreeTranslation->alias;
                $beforeUpdateAliasPath = $contentTreeTranslation->alias_path;


                $transaction = Yii::$app->db->beginTransaction();
                if ($contentTreeTranslation->save()) {
                    $aliasChanged = ( ( $contentTreeTranslation->alias != $beforeUpdateAlias )
                        || ( $contentTreeTranslation->alias_path != $beforeUpdateAliasPath ) );

                    if ($aliasChanged) {
                        Console::output("-------------------------------------------------------------------");
                        $linkText = $contentTreeItem->link_id ? "(LINK)" : "";
                        Console::output("ContentTreeTranslation (id = {$contentTreeTranslation->id}) " .
                            "{$linkText} [{$contentTreeTranslation->language}] updated:");
                        Console::output("alias: {$beforeUpdateAlias} => {$contentTreeTranslation->alias}");
                        Console::output("alias_path: {$beforeUpdateAliasPath} => {$contentTreeTranslation->alias_path}");
                    }

                    if ($contentTreeItem->link_id) {
                        $transaction->commit();
                        continue;
                    }

                    $needUpdateFmiCount = 0;
                    $updatedFmiCount = 0;


                    //Update each individual file manager item
                    $fileManagerItems = FileManagerItem::find()
                        ->byTable($contentTreeItem->table_name)
                        ->byRecordId($contentTreeItem->record_id)
                        ->byLanguage($contentTreeTranslation->language)
                        ->all();

                    $aliasPath = $contentTreeTranslation->alias_path;

                    if ($aliasChanged && $fileManagerItems) {
                        Console::output("Updating file_manager_item table for language: {$contentTreeTranslation->language}, ContentTree id = {$contentTreeItem->id}");
                    }

                    foreach ($fileManagerItems as $fileManagerItem) {
                        $fmiPath = null;

                        $fileName = substr($fileManagerItem->path, strrpos($fileManagerItem->path, '/') + 1);

                        $correctPath = $contentTreeTranslation->language . '/' . $aliasPath . '/' . $fileName;
                        if ($correctPath != $fileManagerItem->path) {
                            $needUpdateFmiCount++;

                            $fileManagerItem->oldPath = $fileManagerItem->path;
                            $fileManagerItem->path = $correctPath;

                            $fmiTransaction = Yii::$app->db->beginTransaction();

                            if ($fileManagerItem->save()) {
                                try {
                                    if (!file_exists($correctPath)) {
                                        //Copy file storage item instead of renaming, since other file manager item might have the same path
                                        $fileManagerItem->copyFileStorageItem();
                                    }
                                    $fmiTransaction->commit();
                                    $updatedFmiCount++;
                                } catch (\Exception $exception) {
                                    $fmiTransaction->rollBack();
                                    if ($exception->getCode() == 404) {
                                        array_push($failedFmiItemIds, $fileManagerItem->id);
                                        array_push($notFoundFmiItemIds, $fileManagerItem->id);
                                    }
                                    Console::output('Failed to updating file manager item (id=' . $fileManagerItem->id . '). ContentTree id=' . $contentTreeItem->id);
                                    Console::output('language: ' . $contentTreeTranslation->language);
                                    Console::output('alias_path: ' . $aliasPath);
                                    Console::output('Old file: ' . $fileManagerItem->oldPath);
                                    Console::output('New File: ' . $fileManagerItem->path);
                                    Console::error($exception->getMessage());
                                    Console::output('----------------------------------');
                                }
                            } else {
                                $fmiTransaction->rollBack();
                                Console::output($fileManagerItem->id);
                                array_push($failedFmiItemIds, $fileManagerItem->id);

                                Console::output('Failed to updating file manager item (id=' . $fileManagerItem->id . '). ContentTree id=' . $contentTreeItem->id);
                                $errors = $fileManagerItem->getErrorSummary(true);
                                foreach ($errors as $error) {
                                    Console::error("{$error}");
                                }
                                Console::output('----------------------------------');
                            }
                        }
                    }
                    if ($aliasChanged && $fileManagerItems) {
                        Console::output("Updated ${updatedFmiCount} from ${needUpdateFmiCount} rows in file_manager_item table");
                    }

                    $transaction->commit();

                } else {
                    $transaction->rollBack();
                    Console::output("Failed to update ContentTreeTranslation ( id = {$contentTreeTranslation->id} )");
                    $errors = $contentTreeTranslation->getErrorSummary(true);
                    foreach ($errors as $error) {
                        Console::error("{$error}");
                    }
                    Console::output('----------------------------------');
                }

            };
        }
        if (count($failedFmiItemIds) > 0) {
            Console::output("Failed to update following file manager item ids: [" . implode(',',
                    $failedFmiItemIds) . "]");
            if (count($notFoundFmiItemIds)) {
                Console::output("File storage item not found for file manager item ids: [" . implode(',',
                        $failedFmiItemIds) . "]");
                if (Console::confirm("Delete these file manager items?")) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        FileManagerItem::deleteAll(['id' => $notFoundFmiItemIds]);
                        $transaction->commit();
                        Console::output("File manager items has been deleted.");
                    } catch (yii\db\Exception $exception) {
                        $transaction->rollBack();
                        Console::error($exception->getMessage());
                    }
                }
            }
        }

        $languageCodes = array_unique(array_values(\Yii::$app->multiSiteCore->websites[$websiteKey]['domains']));
        foreach ($languageCodes as $languageCode) {
            $this->removeEmptyDirectories(Yii::getAlias(FileManagerItem::STORAGE_PATH . $languageCode));
        };
    }

    /**
     *
     *
     * @param $str
     * @return int
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    private function executePdo($str)
    {
        return Yii::$app->db->masterPdo->exec($str);
    }

    /**
     *
     * @param $contentTreesMap ContentTree records grouped by table_name
     *                         and record_id mapped to first available language
     * @param $tableName
     * @param $recordId
     * @return string | null
     * @author Mirian Jinchvelashvili
     */
    private function getLanguageCodeForTimelineEvent($contentTreesMap, $tableName, $recordId)
    {
        return ArrayHelper::getValue($contentTreesMap, $tableName . '.' . $recordId);
    }

    /**
     *
     * Set website_key for website-specific timeline_event records,
     * delete timeline_event records where corresponding content_tree record was not found
     *
     * @author Mirian Jinchvelashvili
     */
    public function actionFixTimelineEvents()
    {
        $websiteMap = [];
        foreach (Yii::$app->multiSiteCore->websites as $websiteKey => $website) {
            foreach (array_unique(array_values($website['domains'])) as $domain) {
                $websiteMap[$domain] = $websiteKey;
            }
        }

        $timelineEvents = TimelineEvent::findClean()
            ->select(['id', 'category', 'record_id'])
            ->andWhere(['website_key' => null])
            ->andWhere(['not', ['record_id' => null]])
            ->asArray()
            ->all();

        $notFoundItemsIds = [];
        $deletedCount = $updatedCount = 0;
        $needUpdateCount = count($timelineEvents);

        if ($needUpdateCount > 0) {
            $ctQuery = ContentTree::findClean()
                ->with('translations')
                ->linkedIdIsNull();

            $contentTreeIds = [];
            $recordIdTableNames = [];
            foreach ($timelineEvents as $timelineEvent) {
                /** @var $timelineEvent TimelineEvent
                 */

                if ($timelineEvent['category'] == "content_tree") {
                    $contentTreeIds[] = $timelineEvent['record_id'];
                } else {
                    $recordIdTableNames[] = ['record_id' => $timelineEvent['record_id'], 'table_name' => $timelineEvent['category']];
                }
            }
            $cts = $ctQuery->andWhere(['or',
                ['id' => $contentTreeIds],
                [
                    'in',
                    ['record_id', 'table_name'],
                    $recordIdTableNames
                ]
            ])->all();

            $ctsMapped = ArrayHelper::map($cts, 'record_id', function ($ctItem) {
                return $ctItem->translations ? $ctItem->translations[0]->language : null;
            }, 'table_name');

            $ctsMapped['content_tree'] = ArrayHelper::map($cts, 'id', function ($ctItem) {
                return $ctItem->translations ? $ctItem->translations[0]->language : null;
            });
        }

        $updateList = [];
        foreach ($timelineEvents as $timelineEvent) {
            $language = $this->getLanguageCodeForTimelineEvent($ctsMapped, $timelineEvent['category'], $timelineEvent['record_id']);
            if ($language) {
                if (isset($websiteMap[$language])) {
                    array_push($updateList, ['id' => $timelineEvent['id'], 'language' => $websiteMap[$language]]);
                } else {
                    Console::error("No website exists for language: \"$language\"");
                }
            } else {
                $notFoundItemsIds[] = $timelineEvent['id'];
                $needUpdateCount--;
            }
        }

        $updatListMap = ArrayHelper::map($updateList, 'id', 'id', 'language');

        foreach ($updatListMap as $websiteKey => $ids) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                TimelineEvent::updateAll(['website_key' => $websiteKey], ['id' => array_keys($ids)]);
                $transaction->commit();
                $updatedCount += count($ids);
            } catch (yii\db\Exception $exception) {
                $transaction->rollBack();
                Console::error($exception->getMessage());
            }
        }

        $updateAction = $needUpdateCount ? true : false;
        $deleteAction = $notFoundItemsIds ? true : false;


        if ($notFoundItemsIds) {
            Console::output("The following ".count($notFoundItemsIds)." timeline event items does not exist any more: [" . implode(',',
                    $notFoundItemsIds) . "]");
            if (Console::confirm("Delete these timeline_event records?")) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    TimelineEvent::deleteAll(['id' => $notFoundItemsIds]);
                    $transaction->commit();
                    $deletedCount = count($notFoundItemsIds);
                } catch (yii\db\Exception $exception) {
                    $transaction->rollBack();
                    Console::error($exception->getMessage());
                }
            }
        }

        Console::output("Task completed successfully");
        if ($updateAction) {
            Console::output("Updated ${updatedCount} from ${needUpdateCount} rows in timeline_event table");
        }
        if ($deleteAction) {
            Console::output("Deleted ${deletedCount} rows in timeline_event table");
        }
        if (!$updateAction && !$deleteAction) {
            Console::output("No changes were made in timeline_event table");
        }
    }
}
