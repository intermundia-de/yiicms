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
 * Class ContentChangeListener
 * @package intermundia\yiicms\behaviors
 */
class ContentChangeListener extends Behavior
{

    public function events()
    {
        return [
            BaseTranslateModel::CHANGE_TITLE => 'changeTitle',
            ContentTreeTranslation::CHANGE_ALIAS_PATH => 'changeAliasPath',
            ContentTreeTranslation::CHANGE_CHILDREN_PATH => 'changeChildrenPath',
        ];
    }


    public function changeTitle($event)
    {
        /** @var $baseTranslateModel BaseTranslateModel */
        $baseTranslateModel = $this->owner;
        $contentTree = $baseTranslateModel->contentTree;
        $language = $baseTranslateModel->language;

        /** @var ContentTreeTranslation $contentTreeTranslation */
        $contentTreeTranslation = $contentTree->getTranslations()->byLanguage($language)->one() ?: new ContentTreeTranslation();
        $contentTreeTranslation->content_tree_id = $contentTree->id;
        $contentTreeTranslation->language = $language;
        $contentTreeTranslation->name = $baseTranslateModel->getTitle();
        $contentTreeTranslation->short_description = $baseTranslateModel->getShortDescription();

        if (!$contentTreeTranslation->save()) {
            throw new \Exception('Error Saving ContentTreeTranslation: ' . VarDumper::dumpAsString($contentTreeTranslation->errors));
        }
    }


    public function changeAliasPath()
    {
        /** @var  $contentTreeTranslation ContentTreeTranslation */
        $contentTreeTranslation = $this->owner;
        $contentTreeTranslation->updateOwnFileManagerItems();
        if ($contentTreeTranslation->children) {
            $contentTreeTranslation->updateChildrenAliasPath();
            $contentTreeTranslation->updateChildrenFileManagerItem();
        }
        $contentTreeTranslation->renameFolder();
    }

    public function changeChildrenPath()
    {
        /** @var  $contentTreeTranslation ContentTreeTranslation */
        $contentTreeTranslation = $this->owner;
        $contentTreeTranslation->updateChildrenAliasPath();
        $contentTreeTranslation->updateChildrenFileManagerItem();
        $oldDir = $contentTreeTranslation->getFileManagerDirectoryPath(array_values($contentTreeTranslation->children)[0]['parent_old_path']);
        $contentTreeTranslation->renameFolder($oldDir);
    }
}
