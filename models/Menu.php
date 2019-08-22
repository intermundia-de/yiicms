<?php

namespace intermundia\yiicms\models;

use Yii;

/**
 * This is the model class for table "{{%menu}}".
 *
 * @property int $id
 * @property string $name
 * @property string $key
 *
 * @property ContentTreeMenu[] $contentTreeMenus
 */
class Menu extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%menu}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'key' => Yii::t('intermundiacms', 'Key'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentTreeMenus()
    {
        return $this->hasMany(ContentTreeMenu::className(), ['menu_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\MenuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\MenuQuery(get_called_class());
    }
}
