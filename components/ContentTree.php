<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 9:48 PM
 */

namespace intermundia\yiicms\components;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;


/**
 * Class ContentTree
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\components
 */
class ContentTree extends Component
{
    /**
     * Format:
     * [
     *      '{table_name}' => ['class' => '\\namespace\\subnamespace\\{TableName}'],
     *      '{table_name2}' => ['class' => '\\namespace\\subnamespace\\{TableName2}']
     * ]
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var array
     */
    public $editableContent = [];
    public $menuOptions = [];
    public $customViews = [];
    public $customContentTreeClass = [];
    public $contentEditingLoginForm = false;

    public function init()
    {
        Yii::setAlias('@cmsCore', Yii::getAlias('@vendor/intermundia/yiicms'));
        $this->editableContent = ArrayHelper::merge([
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_PAGE => [
                'class' => \intermundia\yiicms\models\Page::class,
                'searchableAttributes' => ['title', 'short_description', 'body'],
                'displayName' => Yii::t('intermundiacms', 'Page')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE => [
                'class' => \intermundia\yiicms\models\Website::class,
                'searchableAttributes' => ['title', 'short_description'],
                'displayName' => Yii::t('intermundiacms', 'Website')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_VIDEO_SECTION => [
                'class' => \intermundia\yiicms\models\VideoSection::class,
//                'searchableAttributes' => ['title', 'content_top', 'content_bottom'],
                'searchableAttributes' => [],
                'displayName' => Yii::t('intermundiacms', 'Video Section')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_CONTENT_TEXT => [
                'class' => \intermundia\yiicms\models\ContentText::class,
//                'searchableAttributes' => ['name', 'single_line', 'multi_line'],
                'searchableAttributes' => ['multi_line'],
                'displayName' => Yii::t('intermundiacms', 'Content Text')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_SECTION => [
                'class' => \intermundia\yiicms\models\Section::class,
//                'searchableAttributes' => ['title', 'description'],
                'searchableAttributes' => [],
                'displayName' => Yii::t('intermundiacms', 'Section')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_CAROUSEL => [
                'class' => \intermundia\yiicms\models\Carousel::class,
//                    'searchableAttributes' => ['legal_text_for_patients'],
                'searchableAttributes' => [],
                'displayName' => Yii::t('intermundiacms', 'Carousel')
            ],
            \intermundia\yiicms\models\ContentTree::TABLE_NAME_CAROUSEL_ITEM => [
                'class' => \intermundia\yiicms\models\CarouselItem::class,
//                'searchableAttributes' => ['caption'],
                'searchableAttributes' => [],
                'displayName' => Yii::t('intermundiacms', 'Carousel Item')
            ]
        ], $this->editableContent);
        parent::init();
    }

    public function getClassName($contentType)
    {
        $config = ArrayHelper::getValue($this->editableContent, $contentType);
        if (!$config || !is_array($config)) {
            return null;
        }

        return ArrayHelper::getValue($config, 'class');
    }

    public function getSearchableAttributes($contentType)
    {
        $config = ArrayHelper::getValue($this->editableContent, $contentType);
        if (!$config || !is_array($config)) {
            return null;
        }

        return ArrayHelper::getValue($config, 'searchableAttributes', []);
    }

    public function getEditableClasses()
    {
        $array = [];
        $editableContent = array_filter($this->editableContent, function ($item) {
            return !isset($item['display']) || $item['display'] === true;
        });
        foreach ($editableContent as $contentType => $config) {
            $array[] = [
                'contentType' => $contentType,
                'displayName' => $config['displayName']
            ];
        }
        usort($array, function ($item, $item2) {
            return strcmp($item['displayName'], $item2['displayName']);
        });

        return $array;
    }

    public function getEditableClassesKey()
    {
        $array = [];
        foreach ($this->editableContent as $contentType => $config) {
            $array[$contentType] = [
                'contentType' => $contentType,
                'displayName' => $config['displayName']
            ];
        }

        return $array;
    }

    /**
     * @param $tableName
     * @param $linkId
     * @return string
     */
    public function getIcon($tableName, $linkId = null)
    {
        if ($linkId) {
            return 'fa-link';
        } else {
            switch ($tableName) {
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_WEBSITE:
                    return 'fa-globe';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_PAGE:
                    return 'fa-file-powerpoint-o';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_VIDEO_SECTION:
                    return 'fa-file-video-o';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_CONTENT_TEXT:
                    return 'fa-file-text';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_SECTION:
                    return 'fa-folder-open-o';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_CAROUSEL:
                    return 'fa-film';
                    break;
                case \intermundia\yiicms\models\ContentTree::TABLE_NAME_CAROUSEL_ITEM:
                    return 'fa-file-image-o';
                    break;
            }
        }

        return '';
    }

    public function getDisplayName($tableName)
    {
        return ArrayHelper::getValue($this->editableContent, "$tableName.displayName");
    }

    public function getViewsForTable($tableName)
    {
        $views = array_merge([
            '' => Yii::t('backend', 'Default')
        ], ArrayHelper::getValue($this->customViews, $tableName, []));
        asort($views);

        return $views;
    }

    public function hasCustomViews($tableName)
    {
        return isset($this->customViews[$tableName]);
    }

    /**
     * @param $tableName
     * @param $view
     * @return bool
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function viewExists($tableName, $view)
    {
        $views = Yii::$app->contentTree->getViewsForTable($tableName);

        return (bool)ArrayHelper::getValue($views, $view);
    }

    /**
     * @param $tableName
     * @return mixed
     */
    public function getCustomCssClass($tableName)
    {
        $customClass = ArrayHelper::getValue($this->customContentTreeClass, $tableName);

        return ArrayHelper::getValue($customClass, 'customStyles');
    }
}
