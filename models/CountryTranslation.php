<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "country_translation".
 *
 * @property int $id
 * @property int $country_id
 * @property string $language
 * @property string $name
 * @property int $created_at
 * @property int $updated_at
 * @property int $deleted_at
 * @property int $created_by
 * @property int $updated_by
 * @property int $deleted_by
 *
 * @property Country $country
 * @property User $createdBy
 * @property User $deletedBy
 * @property User $updatedBy
 */
class CountryTranslation extends \yii\db\ActiveRecord
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
        return '{{%country_translation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['country_id', 'language'], 'required'],
            [['country_id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['language'], 'string', 'max' => 12],
            [['name'], 'string', 'max' => 1024],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['deleted_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deleted_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'country_id' => Yii::t('common', 'Country ID'),
            'language' => Yii::t('common', 'Language'),
            'name' => Yii::t('common', 'Name'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'deleted_at' => Yii::t('common', 'Deleted At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_by' => Yii::t('common', 'Updated By'),
            'deleted_by' => Yii::t('common', 'Deleted By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
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
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\CountryTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\CountryTranslationQuery(get_called_class());
    }

    public function markDeleted()
    {
        $this->deleted_at = time();
        $this->deleted_by = Yii::$app->user->identity->id;
        return $this->save();
    }
}
