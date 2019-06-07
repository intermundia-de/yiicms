<?php
/**
 * Created by PhpStorm.
 * User: zura
 * Date: 7/5/18
 * Time: 3:34 PM
 */

namespace intermundia\yiicms\console\controllers;


use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeTranslation;
use intermundia\yiicms\models\Language;
use intermundia\yiicms\models\Search;
use intermundia\yiicms\models\FileManagerItem;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\helpers\FileHelper;

class SyncController extends Controller
{
    const STORAGE_PATH = '@storage/web/source/';

    public function actionSlug()
    {
        $contentTreeTranslations = ContentTreeTranslation::find()
            ->joinWith('contentTree')
            ->orderBy('content_tree.depth')
            ->all();

        $i = 0;
        $f = 0;

        foreach ($contentTreeTranslations as $contentTreeTranslation) {
            if ($contentTreeTranslation->content_tree_id !== 1) {
                $contentTreeTranslation->getBehavior('sluggable')->forceUpdate = true;
                $contentTreeTranslation->oldAlias = $contentTreeTranslation->alias;
                $contentTreeTranslation->oldAliasPath = $contentTreeTranslation->alias_path;

                if ($contentTreeTranslation->save()) {
                    $i++;
                } else {
                    $f++;
                }
            }
        }

        $this->log($i . ' Items have been updated Successfully');
        $this->log($f . ' Items have been failed');

    }

    /**
     *
     *
     * @throws \yii\db\Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function actionSearch()
    {
        $insert = 0;
        $update = 0;
        $delete = 0;
        $tableNames = array_map(function ($tableName) {
            return $tableName['tableName'];
        }, \Yii::$app->contentTree->getEditableClasses());

        foreach ($tableNames as $tableName) {
            $searchAttributes = \Yii::$app->contentTree->getSearchableAttributes($tableName);
            $className = Yii::$app->contentTree->getClassName($tableName);
            $translateClass = $className::getTranslateModelClass();
            $translateModels = $translateClass::find()
                ->joinWith('search')
                ->joinWith('contentTree')
                ->asArray()->all();
            foreach ($translateModels as $translateModel) {

                if ($translateModel['search']) {
                    $searchAttr = array_filter($translateModel['search'], function ($searchable) use ($tableName) {
                        if ($searchable['table_name'] == $tableName) {
                            return $searchable['attribute'];
                        }
                        return false;
                    });
                    $attributes = array_map(function ($searchable) use ($tableName) {
                        return $searchable['attribute'];
                    }, $searchAttr);

                    $editedAttributes = array_diff($searchAttributes, $attributes);
                    $deletedAttributes = array_diff($attributes, $searchAttributes);

                    if (count($editedAttributes) > 0) {
                        foreach ($editedAttributes as $searchAttribute) {
                            $insert++;
                            $data[] = [
                                'content_tree_id' => $translateModel['contentTree']['id'],
                                'table_name' => $tableName,
                                'record_id' => $translateModel[$tableName . '_id'],
                                'language' => $translateModel['language'],
                                'attribute' => $searchAttribute,
                                'content' => strip_tags($translateModel[$searchAttribute])
                            ];
                        }
                    }
                    if (count($deletedAttributes) > 0) {
                        $delete++;
                        Search::deleteAll(['attribute' => $deletedAttributes, 'table_name' => $tableName]);
                    }

                    foreach ($searchAttr as $search) {
                        if (!in_array($search['attribute'],
                                $deletedAttributes) && $search['content'] != strip_tags($translateModel[$search['attribute']])) {
                            $update++;
                            Search::updateAll(['content' => strip_tags($translateModel[$search['attribute']])],
                                'id = ' . $search['id']);
                        }
                    }
                } else {
                    foreach ($searchAttributes as $searchAttribute) {
                        $insert++;
                        $data[] = [
                            'content_tree_id' => $translateModel['contentTree']['id'],
                            'table_name' => $tableName,
                            'record_id' => $translateModel[$tableName . '_id'],
                            'language' => $translateModel['language'],
                            'attribute' => $searchAttribute,
                            'content' => strip_tags($translateModel[$searchAttribute])
                        ];
                    }
                }
            }
        }
        if (isset($data) && count($data) > 0) {
            Search::batchInsert($data);
        }

        $this->log("Inserted " . $insert . " rows in Search Table");
        $this->log("Updated  " . $update . " rows in Search Table");
        $this->log("Deleted  " . $delete . " rows in Search Table");
    }

    public function actionWebsites()
    {
        $websites = Yii::$app->multiSiteCore->websites;

        $transaction = Yii::$app->db->beginTransaction();

        foreach ($websites as $website => $websiteDomains) {
            $dbContentTreeWebsite = ContentTree::findClean()
                ->byTableName(ContentTree::TABLE_NAME_WEBSITE)
                ->byKey($website)
                ->one();
            if (!$dbContentTreeWebsite) {
                try {
                    // Add website
                    Yii::$app->db->createCommand()->insert('{{%website}}', [
                        'created_at' => time(),
                        'created_by' => 1,
                        'updated_at' => time(),
                        'updated_by' => 1,
                    ])->execute();

                    $websiteId = Yii::$app->db->lastInsertID;
                    $this->log("Last inserted ID: " . $websiteId);

                    // Add content tree
                    $dbContentTreeWebsite = new ContentTree();
                    $dbContentTreeWebsite->record_id = $websiteId;
                    $dbContentTreeWebsite->key = $website;
                    $dbContentTreeWebsite->table_name = ContentTree::TABLE_NAME_WEBSITE;
                    if (!$dbContentTreeWebsite->makeRoot()) {
                        echo '<pre>';
                        var_dump($dbContentTreeWebsite->errors);
                        echo '</pre>';
                        $transaction->rollBack();
                        exit;
                    }

                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }

            } else {
//                $this->log("Update tree children: $website");
//                ContentTree::updateAll(['website' => $dbContentTreeWebsite->id],
//                    "lft >= :lft AND rgt <= :rgt AND table_name != :tableName", [
//                        'lft' => $dbContentTreeWebsite->lft,
//                        'rgt' => $dbContentTreeWebsite->rgt,
//                        'tableName' => ContentTree::TABLE_NAME_WEBSITE
//                    ]);
                $dbContentTreeWebsite->website = $dbContentTreeWebsite->id;
                if (!$dbContentTreeWebsite->save()) {
                    echo '<pre>';
                    var_dump($dbContentTreeWebsite->errors);
                    echo '</pre>';
                    exit;
                }
            }


            $domains = array_unique($websiteDomains['domains']);

            // Add website translations
            foreach ($domains as $domain => $lang) {
                $websiteTranslation = ContentTreeTranslation::find()->andWhere([
                    'language' => $lang,
                    'content_tree_id' => $dbContentTreeWebsite->id,
                ])->one();
                $this->log("Selecting ContentTreeTranslation for website: \"$website\" and language: \"$lang\"");
                if (!$websiteTranslation) {
                    $this->log("Website translation for \"$lang\" does not exist. Creating...");


                    Yii::$app->db->createCommand()->insert('{{%website_translation}}', [
                        'name' => $domain,
                        'title' => $domain,
                        'language' => $lang,
                        'website_id' => $dbContentTreeWebsite->record_id,
                    ])->execute();

                    // Add content tree translations
                    Yii::$app->db->createCommand()->insert('{{%content_tree_translation}}', [
                        'language' => $lang,
                        'content_tree_id' => $dbContentTreeWebsite->id,
                        'alias' => $website,
                        'alias_path' => $website,
                        'name' => $website
                    ])->execute();
                }
            }
            $this->log("Saved website: $website");
        }
        $transaction->commit();
        $this->log("Committed");
    }

    public function actionLanguages()
    {
        $websites = Yii::$app->multiSiteCore->websites;
        $languages = [];
        foreach ($websites as $website) {
            $languages = array_merge($languages, $website['domains']);
        }

        $codes = array_map(function ($item) {
            return $item['code'];
        }, Language::find()->select('code')->asArray()->all());

        $toBeInsertedLanguages = array_diff(array_values(array_unique($languages)), $codes);

        $insertData = [];

        foreach ($toBeInsertedLanguages as $lang) {
            $data['code'] = $lang;
            $data['name'] = $lang;
            $data['created_at'] = time();
            $data['updated_at'] = time();
            $data['updated_by'] = 1;
            $data['created_by'] = 1;
            $insertData[] = $data;
        }

        if ($insertData) {
            Yii::$app->db->createCommand()->batchInsert(
                Language::tableName(),
                array_keys($data),
                $insertData
            )->execute();
            Console::output("Languages [" . implode(' , ',
                    $toBeInsertedLanguages) . "] has been added. Add User friendly names in DB");
        } else {
            Console::output("Languages has already added");
        }


    }

    /**
     * @param null $language
     * @throws \yii\base\Exception
     * @author Mirian Jintchvelashvili <mirianjinchvelashvili@gmail.com>
     *
     */
    public function actionFileManagerItemPath($language = null)
    {

        $languageCodes = array_map(function ($item) {
            return $item['code'];
        }, Language::find()->select('code')->asArray()->all());
        if ($language) {
            if (!in_array($language, $languageCodes)) {
                Console::error("Language code '{$language}' not found in 'language' table.");
                Console::output("Available language codes are: [" .
                    implode(' , ', $languageCodes) . "]");
                exit;
            }
            $languages = [$language];
        } else {
            $languages = $languageCodes;
        }

        foreach ($languages as $language) {
            Console::output("Updating file_manager_item table for language: {$language}");

            $contentTreeItems = ContentTree::findClean()
                ->joinWith([
                    'translation' => function ($q) use ($language) {
                        $q->where(['language' => $language]);
                    },
                ])
                ->linkedIdIsNull()
                ->all();

            $updatedCount = 0;
            $needUpdateCount = 0;
            $failedItemIds = [];
            $notFoundItemIds = [];

            foreach ($contentTreeItems as $contentTreeItem) {
                $fileManagerItems = FileManagerItem::find()
                    ->byTable($contentTreeItem->table_name)
                    ->byRecordId($contentTreeItem->record_id)
                    ->byLanguage($language)
                    ->all();

                $aliasPath = $contentTreeItem->translation->alias_path;

                foreach ($fileManagerItems as $fileManagerItem) {
                    $fmiPath = null;

                    $fileName = substr($fileManagerItem->path, strrpos($fileManagerItem->path, '/') + 1);

                    $correctPath = $language . '/' . $aliasPath . '/' . $fileName;
                    if ($correctPath != $fileManagerItem->path) {
                        $needUpdateCount++;

                        $fileManagerItem->oldPath = $fileManagerItem->path;
                        $fileManagerItem->path = $correctPath;

                        $transaction = Yii::$app->db->beginTransaction();

                        if ($fileManagerItem->save()) {
                            try {
                                if (!file_exists($correctPath)) {
                                    //Copy file storage item instead of renaming, since other file manager item might have the same path
                                    $fileManagerItem->copyFileStorageItem();

                                    $oldFileReferences = FileManagerItem::find()
                                        ->where(['path' => $fileManagerItem->oldPath])
                                        ->andWhere(['<>', 'id', $fileManagerItem->id])
                                        ->all();

                                    if (!$oldFileReferences) {
                                        // Delete old file.
                                        $filePath = Yii::getAlias(FileManagerItem::STORAGE_PATH) . $fileManagerItem->oldPath;
                                        $dir = substr($filePath, 0, strrpos($filePath, '/'));
                                        FileHelper::unlink($filePath);

                                        //If directory is empty delete it. 2 for ./ and ../
                                        if (count(scandir($dir)) == 2) {
                                            rmdir($dir);
                                        }
                                    }
                                }
                                $transaction->commit();
                                $updatedCount++;
                            } catch (\Exception $exception) {
                                $transaction->rollBack();
                                $failedItemIds[] = $fileManagerItem->id;
                                if ($exception->getCode() == 404) {
                                    $notFoundItemIds[] = $fileManagerItem->id;
                                }
                                Console::output('Failed to updating file manager item (id=' . $fileManagerItem->id . '). ContentTreeItem id=' . $contentTreeItem->id);
                                Console::output('language: ' . $language);
                                Console::output('alias_path: ' . $aliasPath);
                                Console::output('Old file: ' . $fileManagerItem->oldPath);
                                Console::output('New File: ' . $fileManagerItem->path);
                                Console::error($exception->getMessage());
                                Console::output('----------------------------------');
                            }
                        } else {
                            $transaction->rollBack();
                            $failedItemIds[] = $fileManagerItem->id;

                            Console::output('Failed to updating file manager item (id=' . $fileManagerItem->id . '). ContentTreeItem id=' . $contentTreeItem->id);
                            $errors = $fileManagerItem->getErrorSummary(true);
                            foreach ($errors as $error) {
                                Console::error("{$error}");
                            }
                            Console::output('----------------------------------');
                        }
                    }
                }
            }
            $this->log("Updated ${updatedCount} from ${needUpdateCount} rows in file_manager_item Table for language {$language}");
            if (count($failedItemIds) > 0) {
                $this->log("Failed to update following file manager item ids: [" . implode(' , ',
                        $failedItemIds) . "]");
                if (count($notFoundItemIds)) {
                    $this->log("File storage item not found for file manager item ids: [" . implode(' , ',
                            $failedItemIds) . "]");

                    $answer = readline("Delete these file manager items? (Y/N): ");
                    if ($answer == 'Y') {
                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            FileManagerItem::deleteAll(['id' => $notFoundItemIds]);
                            $transaction->commit();
                            Console::output("File manager items has been deleted.");
                        } catch (yii\db\Exception $exception) {
                            $transaction->rollBack();
                            Console::error($exception->getMessage());
                        }
                    }
                }
            }
            Console::output('----------------------------------');
        }
    }

    private function log($message, $type = 'log')
    {
        Console::output("[ " . date('Y-m-d H:i:s') . " ] [ " . strtoupper($type) . " ] -- " . $message);
    }

}
