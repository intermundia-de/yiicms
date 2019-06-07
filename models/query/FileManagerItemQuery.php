<?php

namespace intermundia\yiicms\models\query;

use intermundia\yiicms\models\FileManagerItem;

/**
 * This is the ActiveQuery class for [[\intermundia\yiicms\models\FileManagerItem]].
 *
 * @see \intermundia\yiicms\models\FileManagerItem
 */
class FileManagerItemQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\FileManagerItem[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\FileManagerItem|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $tableName
     * @return FileManagerItemQuery
     */
    public function byTable($tableName)
    {
        return $this->andWhere([FileManagerItem::tableName() . '.table_name' => $tableName]);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $recordId
     * @return FileManagerItemQuery
     */
    public function byRecordId($recordId)
    {
        return $this->andWhere([FileManagerItem::tableName() . '.record_id' => $recordId]);
    }

    /**
     * @param $language
     * @return $this
     */
    public function byLanguage($language)
    {
        return $this->andWhere([FileManagerItem::tableName() . '.language' => $language]);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $columnNames
     * @return FileManagerItemQuery
     */
    public function byColumns($columnNames)
    {
        return $this->andWhere([FileManagerItem::tableName() . '.column_name' => $columnNames]);
    }

    /**
     * @param $id
     * @return FileManagerItemQuery
     */
    public function byId($id)
    {
        return $this->andWhere([FileManagerItem::tableName() . '.id' => $id]);
    }

}
