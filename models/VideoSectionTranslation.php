<?php

namespace intermundia\yiicms\models;

use intermundia\yiicms\behaviors\FileManagerItemBehavior;
use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%video_section_translation}}".
 *
 * @property int $id
 * @property string $language
 * @property int $video_section_id
 * @property string $title
 * @property string $file
 * @property string $content_top
 * @property string $content_bottom
 *
 * @property Language $language0
 * @property VideoSection $videoSection
 */
class VideoSectionTranslation extends BaseTranslateModel
{
    /** @var UploadedFile */
    public $videoFile = null;
    public $videoFile_deleted = null;

    /** @var UploadedFile */
    public $mobileVideoFile = null;
    public $mobileVideoFile_deleted = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_section_translation}}';
    }

//    public function behaviors()
//    {
//        return [
//            'picture' => [
//                'class' => FileUploadBehavior::class,
//                'attribute' => 'file',
//                'filePath' => '@storage/web/source/[[attribute_alias_path]]/[[attribute_language]]/file_[[filename]].[[extension]]',
//                'fileUrl' => Yii::getAlias('@storageUrl') . '/source/[[attribute_alias_path]]/[[attribute_language]]/file_[[filename]].[[extension]]',
//            ],
//        ];
//    }

    public function behaviors()
    {
        return array_merge([
            FileManagerItemBehavior::class => [
                'class' => FileManagerItemBehavior::class,
                'columnNames' => [
                    'videoFile' => 'videoFile',
                    'mobileVideoFile' => 'mobileVideoFile'
                ],
            ],
        ], parent::behaviors());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['video_section_id',], 'integer'],
            [['content_top', 'content_bottom'], 'string'],
            [['videoFile_deleted', 'mobileVideoFile_deleted'], 'safe'],
            [['language'], 'string', 'max' => 12],
            [['title'], 'string', 'max' => 255],
            [['videoFile'], 'file', 'maxFiles' => 20, 'skipOnEmpty' => true, 'extensions' => 'mp4, ogg, ogv, webm'],
            [
                ['mobileVideoFile'],
                'file',
                'maxFiles' => 20,
                'skipOnEmpty' => true,
                'extensions' => 'mp4, ogg, ogv, webm'
            ],
            [
                ['language'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Language::class,
                'targetAttribute' => ['language' => 'code']
            ],
            [
                ['video_section_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => VideoSection::class,
                'targetAttribute' => ['video_section_id' => 'id']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'language' => Yii::t('common', 'Language'),
            'video_section_id' => Yii::t('common', 'Video Section ID'),
            'title' => Yii::t('common', 'Title'),
            'file' => Yii::t('common', 'File'),
            'content_top' => Yii::t('common', 'Content Top'),
            'content_bottom' => Yii::t('common', 'Content Bottom'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage0()
    {
        return $this->hasOne(Language::class, ['code' => 'language']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideoSection()
    {
        return $this->hasOne(VideoSection::class, ['id' => 'video_section_id']);
    }

    /**
     * {@inheritdoc}
     * @return \intermundia\yiicms\models\query\VideoSectionTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \intermundia\yiicms\models\query\VideoSectionTranslationQuery(get_called_class());
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getShortDescription()
    {
        return null;
    }

    public function getModelClass()
    {
        return VideoSection::class;
    }

    public function getForeignKeyNameOnModel()
    {
        return 'video_section_id';
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id' => $this->id,
            'video_section_id' => $this->video_section_id,
            'language' => $this->language,
            'title' => $this->title,
            'file' => $this->file,
            'content_top' => $this->content_top,
            'content_bottom' => $this->content_bottom
        ];
    }
}
