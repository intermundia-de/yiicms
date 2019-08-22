<?php

namespace intermundia\yiicms\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "{{%continent}}".
 *
 * @property int $id
 * @property string $code
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
 * @property ContinentTranslation[] $translations
 * @property ContinentTranslation $activeTranslation
 * @property Country[] $countries
 */
class Continent extends \yii\db\ActiveRecord
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
        return '{{%continent}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['code'], 'string', 'max' => 3],
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
            'id' => Yii::t('intermundiacms', 'ID'),
            'code' => Yii::t('intermundiacms', 'Code'),
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
    public function getTranslations()
    {
        return $this->hasMany(ContinentTranslation::className(), ['continent_id' => 'id'])->notDeleted();
    }

    public function getActiveTranslation()
    {
        return $this->hasOne(ContinentTranslation::className(), ['continent_id' => 'id'])
            ->byLanguage(Yii::$app->language)
            ->notDeleted();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountries()
    {
        return $this->hasMany(Country::className(), ['continent_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContinentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\ContinentQuery(get_called_class());
    }

    public function saveWithTranslations($array, $languages = null)
    {
        $languages = array_keys($languages);
        $dbTransaction = Yii::$app->db->beginTransaction();
        if (!$this->save()) {
            $dbTransaction->rollBack();
            return false;
        }
        $translationsArray = $array['ContinentTranslation'];
        $translationModels = $this->translations;
        if ($translationModels) {
            $translationModels = ArrayHelper::index($translationModels, 'language');
        }
        foreach ($translationsArray as $language => $value) {
            if (!in_array($language, $languages) || !$value['name']) {
                $dbTransaction->rollBack();
                return false;
            }
            $translationModel = $translationModels ? $translationModels[$language] : new ContinentTranslation();
            $translationModel->name = $value['name'];
            $translationModel->continent_id = $this->id;
            $translationModel->language = $language;
            if (!$translationModel->save()) {
                $dbTransaction->rollBack();
                return false;
            }
        }
        $dbTransaction->commit();
        return true;
    }

    public function markDeleted()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $this->deleted_at = time();
        $this->deleted_by = Yii::$app->user->identity->id;

        foreach ($this->translations as $translation) {
            if (!$translation->markDeleted()) {
                Yii::error("Error deleting continent translation for id {$translation->id}. Errors: " . VarDumper::dumpAsString($translation->errors));
                $transaction->rollBack();
                return false;
            }
        }
        if (!$this->save()) {
            Yii::error("Error deleting continent for id {$this->id}. Errors: " . VarDumper::dumpAsString($translation->errors));
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    public static function getContinentsMapped()
    {
        return ArrayHelper::map(self::find()
            ->with(['activeTranslation'])
            ->notDeleted()
            ->all(),'id','activeTranslation.name');
    }
}
