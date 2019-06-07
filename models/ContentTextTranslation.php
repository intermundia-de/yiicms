<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use Yii;
use yii\helpers\StringHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%content_text_translation}}".
 *
 * @property int $id
 * @property int $content_text_id
 * @property string $language
 * @property string $name
 * @property string $single_line
 * @property string $multi_line
 * @property string $multi_line2
 *
 * @property ContentText $contentText
 * @property Language $language0
 */
class ContentTextTranslation extends BaseTranslateModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%content_text_translation}}';
    }

    /**
     * This attribute is used for file input. You have to add image_deleted in your rules for deleting already
     * saved files
     *
     * @author Guga Grigolia <grigolia.guga@gmail.com>
     * @var UploadedFile|FileManagerItem
     */
    public $image;

    public $image_deleted;

    public function behaviors()
    {
        return array_merge([
            FileManagerItemBehavior::class => [
                'class' => FileManagerItemBehavior::class,
                'columnNames' => ['image' => 'image'],
            ]
        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_text_id'], 'integer'],
            [['multi_line', 'multi_line2'], 'string'],
            [['image_deleted'], 'safe'],
            [['language'], 'string', 'max' => 12],
            [['name'], 'string', 'max' => 1024],
            ['image', 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg, svg'],
            [['single_line'], 'string', 'max' => 2048],
            [['content_text_id'], 'exist', 'skipOnError' => true, 'targetClass' => ContentText::class, 'targetAttribute' => ['content_text_id' => 'id']],
            [['language'], 'exist', 'skipOnError' => true, 'targetClass' => Language::class, 'targetAttribute' => ['language' => 'code']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'content_text_id' => Yii::t('common', 'Content Text ID'),
            'language' => Yii::t('common', 'Language'),
            'name' => Yii::t('common', 'Name'),
            'single_line' => Yii::t('common', 'Single Line'),
            'multi_line' => Yii::t('common', 'Multi Line'),
            'multi_line2' => Yii::t('common', 'Multi Line 2'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContentText()
    {
        return $this->hasOne(ContentText::class, ['id' => 'content_text_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage0()
    {
        return $this->hasOne(Language::class, ['code' => 'language']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\ContentTextTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\ContentTextTranslationQuery(get_called_class());
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return ContentText::class;
    }

    /**
     * @return string
     */
    public function getForeignKeyNameOnModel()
    {
        return 'content_text_id';
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->single_line ? StringHelper::truncate($this->single_line, 1000) : StringHelper::truncate($this->multi_line, 1000);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id' => $this->id,
            'content_text_id' => $this->content_text_id,
            'language' => $this->language,
            'name' => $this->name,
            'single_line' => $this->single_line,
            'multi_line' => $this->multi_line,
            'multi_line2' => $this->multi_line2
        ];
    }
}
