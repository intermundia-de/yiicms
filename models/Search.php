<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\ContentTreeQuery;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "search".
 *
 * @property int $id
 * @property int $content_tree_id
 * @property string $table_name
 * @property string $record_id
 * @property string $language
 * @property string $attribute
 * @property string $content
 *
 * @property ContentTree $contentTree
 * @property ContentTree[] $linkedContentTrees
 */
class Search extends \yii\db\ActiveRecord
{

    const ATTRIBUTE_ORDER = ["'title'", "'name'", "'description'", "'short_description'", "'body'", "'content_top'"];
    const TABLE_NAME_ORDER = ["'page'", "'section'", "'video_section'", "'content_text'"];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'search';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_tree_id'], 'integer'],
            [['content'], 'string'],
            [['table_name', 'record_id', 'language', 'attribute'], 'string', 'max' => 255],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'content_tree_id' => Yii::t('intermundiacms', 'Content Tree ID'),
            'table_name' => Yii::t('intermundiacms', 'Table Name'),
            'record_id' => Yii::t('intermundiacms', 'Record ID'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'attribute' => Yii::t('intermundiacms', 'Attribute'),
            'content' => Yii::t('intermundiacms', 'Content'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\SearchQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\SearchQuery(get_called_class());
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \yii\db\ActiveQuery
     */
    public function getContentTree()
    {
        return $this->hasOne(ContentTree::class, ['id' => 'content_tree_id']);
    }

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \yii\db\ActiveQuery
     */
    public function getLinkedContentTrees()
    {
        return $this->hasMany(ContentTree::class, ['link_id' => 'content_tree_id']);
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $data
     * @throws \yii\db\Exception
     */
    public static function batchInsert($data)
    {
        Yii::$app->db->createCommand()
            ->batchInsert(Search::tableName(),
                ['content_tree_id', 'table_name', 'record_id', 'language', 'attribute', 'content'],
                $data)
            ->execute();
    }

    public static function batchUpdate($data)
    {

    }

    /**
     * Creates search query
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $params
     * @param string $formName
     * @return query\SearchQuery
     */
    public function search($params, $formName = '')
    {

        $query = Search::find()
            ->with([
                'contentTree' => function ($query) {
                    /** @var ContentTreeQuery $query */
                    $query->notHidden()->notDeleted();
                },
                'contentTree.defaultTranslation',
                'contentTree.currentTranslation',
                'linkedContentTrees' => function ($query) {
                    /** @var ContentTreeQuery $query */
                    $query->notHidden()->notDeleted();
                },
                'linkedContentTrees.defaultTranslation',
                'linkedContentTrees.currentTranslation'
            ]);

        if (!($this->load($params, $formName) && $this->validate())) {
            return $query;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'language' => [Yii::$app->language, Yii::$app->websiteMasterLanguage]
        ]);

        if ($this->table_name) {
            $table_name = implode("_", array_map('strtolower', explode(' ', $this->table_name)));
            $query->andFilterWhere(['like', 'search.table_name', $table_name]);
        }

        $query->andFilterWhere(['like', 'search.content', $this->content])
            ->andFilterWhere(['like', 'search.attribute', $this->attribute])
            ->orderBy([
                new \yii\db\Expression('FIELD (search.table_name, ' . implode(',', self::TABLE_NAME_ORDER) . ')')
            ])
            ->orderBy([
                new \yii\db\Expression('FIELD (search.attribute, ' . implode(',', self::ATTRIBUTE_ORDER) . ')')
            ]);


        return $query;
    }
}
