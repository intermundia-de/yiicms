<?php
/**
 * User: zura
 * Date: 6/19/18
 * Time: 4:31 PM
 */

namespace intermundia\yiicms\widgets;

use intermundia\yiicms\components\NestedSetModel;
use intermundia\yiicms\models\ContentTree;
use intermundia\yiicms\models\ContentTreeMenu;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use intermundia\yiicms\models\Menu;


/**
 * Class NestedMenu
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\widgets
 */
class NestedMenu extends Nav
{
    public $items = [];

    public $options = ['class' => 'nav navbar-nav'];

    public $encodeLabels = false;

    public $dropDownCaret = '';

    public $dropDownParentUrl = true;

    public $customCaret = true;

    public $activateParents = true;

    public $subMenuOnHover = true;

    public $mobile = false;

    /**
     *
     *
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public function init()
    {
        $this->items = self::getItemsForFrontMenu();
        parent::init();
    }

    /**
     * Renders a widget's item.
     *
     * @param string|array $item the item to render.
     * @return string the rendering result.
     * @throws InvalidConfigException
     */
    public function renderItem($item)
    {
        if (is_string($item)) {
            return $item;
        }
        if (!isset($item['label'])) {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        $encodeLabel = isset($item['encode']) ? $item['encode'] : $this->encodeLabels;
        $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
        $options = ArrayHelper::getValue($item, 'options', []);
        $items = ArrayHelper::getValue($item, 'items');
        $url = ArrayHelper::getValue($item, 'url', '#');
        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);

        if (isset($item['active'])) {
            $active = ArrayHelper::remove($item, 'active', false);
        } else {
            $active = $this->isItemActive($item);
        }

        if (empty($items)) {
            $items = '';
        } else {
            $linkOptions['data-toggle'] = 'dropdown';
            Html::addCssClass($options, ['widget' => 'dropdown']);
            Html::addCssClass($linkOptions, ['widget' => 'dropdown-toggle']);
            if ($this->dropDownCaret !== '') {
                $label .= ' ' . $this->dropDownCaret;
            }
            if (is_array($items)) {
                $items = $this->isChildActive($items, $active);
                $items = $this->renderDropdown($items, $item);
            }
        }

        if ($active) {
            Html::addCssClass($options, 'active');
        }

        if (isset($item['items']) && !$this->dropDownParentUrl) {
            return Html::tag('li', Html::a($label, '#', $linkOptions) . $items, $options);
        }

        return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
    }

    /**
     *
     *
     * @param array $tableNames
     * @param array $fields
     * @param array $extraFields
     * @param array $appendParams
     * @return array
     * @throws \Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public static function getItemsForFrontMenu($tableNames = [ContentTree::TABLE_NAME_PAGE], $fields = null, $extraFields = [], $appendParams = [])
    {
        $contentTreeItems = ContentTree::find()
            ->notHidden()
            ->notDeleted()
            ->withTranslations(true)
            ->leftJoin(ContentTreeMenu::tableName(),
                ContentTreeMenu::tableName() . '.content_tree_id = ' . ContentTree::tableName() . '.id')
            ->leftJoin(Menu::tableName(), Menu::tableName() . '.id = ' . ContentTreeMenu::tableName() . '.menu_id')
            ->andWhere([
                'or',
                [
                    Menu::tableName() . '.key' => 'header',
                    ContentTree::tableName() . '.table_name' => $tableNames
                ],
                [
                    ContentTree::tableName() . '.table_name' => [ContentTree::TABLE_NAME_WEBSITE]
                ]
            ])
            ->orderBy('lft')
            ->all();

        return NestedSetModel::getMenuTree($contentTreeItems, $fields, $extraFields, $appendParams);
    }

}
