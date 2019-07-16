<?php

namespace intermundia\yiicms\models;

use common\base\MultiModel;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\VarDumper;

/**
 * Class ContrentMultiModel
 * @package intermundia\yiicms\models
 */
class ContentMultiModel extends MultiModel
{
    const BASE_MODEL = 'baseModel';
    const BASE_TRANSLATION_MODEL = 'baseTrasnlationModel';
    const CONTENT_TREE_MODEL = 'contentTreeModel';

    /**
     * @param bool $runValidation
     * @return bool|void
     * @throws \Exception
     * @var $baseTranslationModel BaseTranslateModel
     * @var $baseModel BaseModel
     */
    public function save($runValidation = true)
    {
        $baseTranslationModel = $this->getBaseTranslationModel();
        // Save BaseModel
        $baseModel = $this->getBaseModel();
        $baseModelisNewRecord = $baseModel->isNewRecord;
        if (!$baseModel->save()){
            return false;
        }

        /** Set forenignKey_id to baseTranslationModel*/
        $foreignKeyName = $baseTranslationModel->getForeignKeyNameOnModel();
        $baseTranslationModel->$foreignKeyName = $baseModel->id;

        $this->saveContentTree();

        if (!$baseTranslationModel->save()){
            return false;
        }

        return true;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function saveContentTree()
    {
        $baseModel = $this->getBaseModel();
        $contentTree = $this->getContentTreeModel();
        if (!$contentTree->isNewRecord) {
            return $contentTree->save();
        }
        
        $parentContentTree = ContentTree::find()
            ->byId($this->getBaseTranslationModel()->parentContentId)
            ->one();

        $contentTree->table_name = $baseModel::getFormattedTableName();
        $contentTree->record_id = $baseModel->id;
        if (!$contentTree->appendTo($parentContentTree)) {
            throw new \Exception('Error Saving Content Tree: ' . VarDumper::dumpAsString($contentTree->errors));
        }

    }

    /**
     * @param array $data
     * @param string $formName
     * @return bool
     */
    public function load($data, $formName = '')
    {
        foreach ($this->models as $k => &$model) {
            /** BaseModel and ContentTreeModel is optional
             *  BaseTranslateModel is required.*/
            if (!isset($data[$model->formName()]) && $k != self::BASE_TRANSLATION_MODEL) {
                continue;
            }
            if (!$model->load($data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return BaseModel | null
     */
    public function getBaseModel()
    {
        return $this->getModel(self::BASE_MODEL);
    }

    /**
     * @return BaseTranslateModel | null
     */
    public function getBaseTranslationModel()
    {
        return $this->getModel(self::BASE_TRANSLATION_MODEL);
    }

    /**
     * @return ContentTree|null
     */
    public function getContentTreeModel()
    {
        return $this->getModel(self::CONTENT_TREE_MODEL);
    }
}
