<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\models\query\CountryTranslationQuery;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "country".
 *
 * @property int $id
 * @property int $continent_id
 * @property int $status
 * @property string $iso_code_1
 * @property string $iso_code_2
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
 * @property CountryTranslation[] $translations
 * @property CountryTranslation $activeTranslation
 * @property Continent $continent
 */
class Country extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class
        ];
    }

    public static function getStatuses()
    {
        return [
            1 => Yii::t('backend', 'Active'),
            0 => Yii::t('backend', 'Disabled')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by','continent_id'], 'integer'],
            [['continent_id'], 'required'],
            [['iso_code_1', 'iso_code_2'], 'string', 'max' => 3],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_by' => 'id']],
            [['deleted_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['deleted_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['updated_by' => 'id']],
            [['continent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Continent::class, 'targetAttribute' => ['continent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'continent_id' => Yii::t('common', 'Continent'),
            'status' => Yii::t('common', 'Status'),
            'iso_code_1' => Yii::t('common', 'Iso Code 1'),
            'iso_code_2' => Yii::t('common', 'Iso Code 2'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getTranslations()
    {
        /** @var CountryTranslationQuery $query */
        $query = $this->hasMany(CountryTranslation::className(), ['country_id' => 'id']);
        return $query->notDeleted();
    }

    public function getActiveTranslation()
    {
        /** @var CountryTranslationQuery $query */
        $query = $this->hasOne(CountryTranslation::className(), ['country_id' => 'id']);
        return $query
            ->byLanguage(Yii::$app->language)
            ->notDeleted();
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\CountryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\CountryQuery(get_called_class());
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $array
     * @param null $languages
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveWithTranslations($array, $languages = null)
    {
        $languages = array_keys($languages);
        $dbTransaction = Yii::$app->db->beginTransaction();
        if (!$this->save()) {
            $dbTransaction->rollBack();
            return false;
        }
        $translationsArray = $array['CountryTranslation'];
        $translationModels = $this->translations;
        if ($translationModels) {
            $translationModels = ArrayHelper::index($translationModels, 'language');
        }
        foreach ($translationsArray as $language => $value) {
            if (!in_array($language, $languages) || !$value['name']) {
                $dbTransaction->rollBack();
                return false;
            }
            $translationModel = $translationModels ? $translationModels[$language] : new CountryTranslation();
            $translationModel->name = $value['name'];
            $translationModel->country_id = $this->id;
            $translationModel->language = $language;
            if (!$translationModel->save()) {
                $dbTransaction->rollBack();
                return false;
            }
        }
        $dbTransaction->commit();
        return true;
    }

    /**
     *
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return bool
     * @throws \yii\db\Exception
     */
    public function markDeleted()
    {
        $transaction = Yii::$app->db->beginTransaction();
        $this->deleted_at = time();
        $this->deleted_by = Yii::$app->user->identity->id;

        foreach ($this->translations as $translation) {
            if (!$translation->markDeleted()) {
                Yii::error("Error deleting country translation for id {$translation->id}. Errors: " . VarDumper::dumpAsString($translation->errors));
                $transaction->rollBack();
                return false;
            }
        }
        if (!$this->save()) {
            Yii::error("Error deleting country for id {$this->id}. Errors: " . VarDumper::dumpAsString($translation->errors));
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    public static function getActiveCountriesSorted()
    {
        $isoCodes = getenv('COUNTRY_SORT_ISO_CODES');
        $isoCodes = explode(',',$isoCodes);

        $countries = Country::find()
            ->innerJoin(CountryTranslation::tableName(),CountryTranslation::tableName().'.country_id = ' . Country::tableName().'.id')
            ->with(['activeTranslation'])
            ->active()
            ->notDeleted();

        if($isoCodes) {
            $orderBy = [];
            foreach($isoCodes as $code){
                $orderBy[Country::tableName().".`iso_code_1` = '{$code}'"] = SORT_DESC;
            }
            $orderBy[CountryTranslation::tableName().".`name`"] = SORT_ASC;
            $countries = $countries->orderBy($orderBy);
        }

        $countries = $countries->all();
        return $countries;
    }
}
