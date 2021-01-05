<?php

namespace intermundia\yiicms\components;


use Exception;
use yii\helpers\ArrayHelper;

/**
 * Class NestedSetModel
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\components
 */
class NestedSetModel
{

    /**
     * Main data (result set) to work on
     *
     * @var array
     */
    protected $data = [];

    /**
     * Keys (with optional mappings) which will be used to extract properties from record
     *
     * @var array
     */
    protected $params = [];

    /**
     * @var
     *
     */
    protected $childOption;
    /**
     * Extra Key - Value pairs which will be added to all nodes
     *
     * @var array
     */
    protected $extraFields = [];


    /**
     *
     * @var array
     */
    protected $addLeaf = false;

    /**
     * Array
     *
     * @var array
     */
    protected $appendParams = [];

    /**
     * Text which will be used as on all leaf nodes with TRUE value
     *
     * $leafName => TRUE
     *
     * @var string
     */
    private $leafName = 'leaf';

    public $count = 0;

    /**
     * Create instance on NestedSetModel with initial data.
     * $data is assumed to be array of associative arrays,
     * where each associative array is record (single item in tree)
     *
     * @param array  $data
     * @param array  $extraFields
     * @param array  $appendParams                         Array where key refers to the property of each node.
     *                                                     Value is array of the following format.
     *                                                     ```
     *                                                     [
     *                                                     'prefix' => '', //prefix of append value
     *                                                     'suffix' => '', //suffix of append value
     *                                                     'attribute' => '', //the attribute of the node.
     *                                                     //The value of this attribute will be used to append
     *                                                     'rawValue' => '' //If attribute will not be provided
     *                                                     rawValue will be used to append
     *                                                     ]
     *                                                     ```
     * @param string $childOption
     */
    public function __construct(array $data = [], array $extraFields = [], $appendParams = [], $childOption = 'items')
    {
        $this->data = $data;
        $this->extraFields = $extraFields;
        $this->appendParams = $appendParams;
        $this->childOption = $childOption;
    }

    /**
     * Supply with data
     *
     * @param array $data
     * @param array $extraFields
     */
    public function supplyData(array $data = [], array $extraFields = [])
    {
        $this->data = $data;
        $this->extraFields = $extraFields;
    }

    /**
     * Get Tree in any level.
     *
     * @param array   $params      "Keys (with optional mappings) which will be used to extract properties from record"
     * @param boolean $addLeaf     "Whether or not add leaf => true to all leafs"
     * @param array   $extraFields Key-value pairs which will be added to all nodes
     * @return array
     * @throws Exception
     */
    public function getTree(array $params = [], $addLeaf = true, $extraFields = [])
    {
        if (count($this->data) === 0) {
            return [];
        }
        if (count($extraFields) > 0) {
            $this->extraFields = $extraFields;
        }
        if (count($params) > 0) {
            $this->params = $params;
        }
        $this->addLeaf = $addLeaf;

        return $this->getNodeInfo(0);

    }

    /**
     * Get all nodes as tree (including given node) which are children of the given node
     *
     * @param int $index
     * @return Node|array
     * @throws Exception
     * @internal param $node
     * @internal param array $params
     * @internal param bool $addLeaf
     * @internal param int $index
     */
    public function getNodeInfo($index = 0)
    {
        if (count($this->data) === 0) {
            throw new Exception("Array is empty");
        }

        $data = $this->data[$index];
        $node = new Node($data['id'], $data);

        for ($i = $index + 1, $l = count($this->data); $i < $l; $i++) {
            $n = new Node($this->data[$i]['id'], $this->data[$i]);
            $node->insert($n);
        }

        return $this->asArray($node);
    }


    public function asArray(Node $node)
    {
        $data = $this->processNode($node);
        if ($node->isLeaf() && $this->addLeaf) {
            $data[$this->leafName] = true;
        }

        foreach ($node->getChildren() as $child) {
            $data[$this->childOption][] = $this->asArray($child);
        }

        return $data;
    }

    protected function processNode(Node $node)
    {
        $obj = [];
        if (count($this->params) === 0) {
            $obj = $node;
        }

        $data = $node->getData();
        foreach ($this->params as $k => $v) {
            if (is_int($k)) {
                $k = $v;
            }
            if (is_callable($v)) {
                $parentData = null;
                if ($node->getParent()) {
                    $parentData = $node->getParent();
                }
                $obj[$k] = call_user_func_array($v, [$data, $parentData]);
            } else if (isset($data[$k])) {
                $obj[$v] = $data[$k];
            }
        }
        foreach ($this->extraFields as $k => $val) {
            $obj[$k] = $val;
        }
        foreach ($this->appendParams as $property => $value) {
            if (!isset($obj[$property])) {
                $obj[$property] = '';
            }
            if (isset($value['attribute'])) {
                $val = ArrayHelper::getValue($data, $value['attribute']);
            } else {
                $val = ArrayHelper::getValue($value, 'rawValue');
            }
            $obj[$property] .= $value['prefix'] . $val . $value['suffix'];
        }
        $node->setProcessedData($obj);

        return $obj;
    }

    public function setLeafName($leafName)
    {
        $this->leafName = $leafName;

        return $this;
    }

    public function setAppendParams($params)
    {
        $this->appendParams = $params;

        return $this;
    }

    /**
     *
     *
     * @param $items
     * @param $fields
     * @param $extraFields
     * @param $appendParams
     * @return mixed
     * @throws Exception
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     */
    public static function getMenuTree($items, $fields = null, $extraFields = [], $appendParams = [])
    {
        if ($fields === null) {
            $fields = ['name' => 'label', 'url' => function ($item) {
                /** @var \intermundia\yiicms\models\ContentTree $item */
                return $item->getUrl(true);
            }, 'active' => function ($item) {
                /** @var \intermundia\yiicms\models\ContentTree $item */

                $url = $item->getUrl();
                $firstParam = \Yii::$app->request->get('nodes');
                if (trim($url, '/') === trim($firstParam, '/')) {
                    return true;
                }
                if (!$firstParam && (!trim($url, '/') || trim($url, '/') === trim(\Yii::$app->defaultRoute, ''))) {
                    return true;
                }

                return false;
            }];
        }
        $nestedSetModel = new NestedSetModel($items, $extraFields, $appendParams);
        $websiteRoot = $nestedSetModel->getTree($fields);

        return ArrayHelper::getValue($websiteRoot, 'items', []);
    }

}
