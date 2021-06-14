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
class SearchBackend extends Search
{
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
                    $query->notDeleted();
                },
                'contentTree.defaultTranslation',
                'contentTree.currentTranslation',
                'linkedContentTrees' => function ($query) {
                    /** @var ContentTreeQuery $query */
                    $query->notDeleted();
                },
                'linkedContentTrees.currentTranslation',
                'linkedContentTrees.defaultTranslation',
            ]);

        if (!($this->load($params, $formName) && $this->validate())) {
            return $query;
        }

        $query->andFilterWhere([
            'id' => $this->id,
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
