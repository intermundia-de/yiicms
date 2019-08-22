<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%continent_translation}}".
 *
 * @property int $id
 * @property int $continent_id
 * @property string $language
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 *
 * @property User $createdBy
 * @property User $deletedBy
 * @property User $updatedBy
 * @property Continent $continent
 */
class ContinentTranslation extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%continent_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['continent_id', 'language'], 'required'],
            [['continent_id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['language'], 'string', 'max' => 12],
            [['name'], 'string', 'max' => 512],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['deleted_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deleted_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['continent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Continent::className(), 'targetAttribute' => ['continent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('intermundiacms', 'ID'),
            'continent_id' => Yii::t('intermundiacms', 'Continent ID'),
            'language' => Yii::t('intermundiacms', 'Language'),
            'name' => Yii::t('intermundiacms', 'Name'),
            'created_at' => Yii::t('intermundiacms', 'Created At'),
            'updated_at' => Yii::t('intermundiacms', 'Updated At'),
            'deleted_at' => Yii::t('intermundiacms', 'Deleted At'),
            'created_by' => Yii::t('intermundiacms', 'Created By'),
            'updated_by' => Yii::t('intermundiacms', 'Updated By'),
            'deleted_by' => Yii::t('intermundiacms', 'Deleted By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDeletedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'deleted_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'updated_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContinent()
    {
        return $this->hasOne(Continent::className(), ['id' => 'continent_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContinentTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\ContinentTranslationQuery(get_called_class());
    }

    public function markDeleted()
    {
        $this->deleted_at = time();
        $this->deleted_by = Yii::$app->user->identity->id;
        return $this->save();
    }
}
