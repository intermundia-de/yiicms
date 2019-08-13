<?php

namespace intermundia\yiicms\models;

use Yii;

/**
 * This is the model class for table "{{%content_tree_menu}}".
 *
 * @property int $id
 * @property int $content_tree_id
 * @property int $menu_id
 * @property int $position
 *
 * @property ContentTree $contentTree
 * @property Menu $menu
 */
class ContentTreeMenu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_tree_menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_tree_id', 'menu_id', 'position'], 'integer'],
            [['content_tree_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentTree::className(), 'targetAttribute' => ['content_tree_id' => 'id']],
            [['menu_id'], 'exist', 'skipOnError' => true, 'targetClass' => Menu::className(), 'targetAttribute' => ['menu_id' => 'id']],
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
            'menu_id' => Yii::t('intermundiacms', 'Menu ID'),
            'position' => Yii::t('intermundiacms', 'Position'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentTree()
    {
        return $this->hasOne(ContentTree::className(), ['id' => 'content_tree_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMenu()
    {
        return $this->hasOne(Menu::className(), ['id' => 'menu_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContentTreeMenuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\ContentTreeMenuQuery(get_called_class());
    }
}
