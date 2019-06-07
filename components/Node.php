<?php
/**
 * User: Zura
 * Date: 12/8/2015
 * Time: 11:12 AM
 */

namespace intermundia\yiicms\components;


/**
 * Class Node
 *
 * @author  Zura Sekhniashvili <zurasekhniashvili@gmail.com>
 * @package intermundia\yiicms\components
 */
class Node
{
    private $key = null;

    private $lft = null;

    private $rgt = null;
    /**
     * Difference between rgt and lft properties
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var integer
     */
    private $difference = null;

    private $data = null;

    private $processedData = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var Node
     */
    private $parent = null;

    /**
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @var Node[]
     */
    private $children = [];

    public function __construct($key, $data)
    {
        $this->key = $key;
        $this->lft = $data['lft'];
        $this->rgt = $data['rgt'];
        $this->difference = $this->rgt - $this->lft;
        $this->data = $data;
    }

    /**
     * Append given node
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param \intermundia\yiicms\components\Node $node
     * @return $this
     */
    public function appendNode(Node $node)
    {
        $this->children[] = $node;
        $node->parent = $this;

        return $this;
    }


    /**
     * Insert given node at specific position
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param \intermundia\yiicms\components\Node $node
     * @return $this
     */
    public function insertAt($position, Node $node)
    {
        array_splice($this->children, $position, 0, [$node]);
        $node->parent = $this;

        return $this;
    }

    /**
     * Create new node with given key and data and append in node. Returns just created node
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param $key
     * @param $data
     * @return \intermundia\yiicms\components\Node
     */
    public function appendData($key, $data)
    {
        $n = new Node($key, $data);
        $n->parent = $this;
        $this->appendNode($n);

        return $n;
    }

    public function insert(Node $node)
    {
        $parent = $this;
        $child = $this->findParentToInsert($node);
        while (!is_int($child)) {
            $parent = $child;
            $child = $child->findParentToInsert($node);
        }

        $parent->insertAt($child, $node);
        return $this;
    }


    protected function findParentToInsert(Node $node)
    {
        $children = $this->children;

        //NEW VERSION USING Binary Search
        $left = 0;
        $right = count($children);

        while ($left < $right) {
            $mid = intval(( $right + $left ) / 2);
            if ($children[$mid]->compare($node) == -1) {
                $left = $mid + 1;
            } else if ($children[$mid]->compare($node) == 1) {
                $right = $mid;
            } else {
                return $children[$mid];
            }
        }

        //OLD WORKING VERSION
//        foreach ($children as $child){
//            self::$count++;
//            if ($child->lft < $node->lft && $child->rgt > $node->rgt){
//                return $child;
//            }
//        }

        return $left;
    }

    /**
     * Get parent node
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return \intermundia\yiicms\components\Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getProcessedData()
    {
        return $this->processedData;
    }

    public function setProcessedData($processedData)
    {
        $this->processedData = $processedData;
        return $this;
    }

    /**
     * Check if node is leaf
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return bool
     */
    public function isLeaf()
    {
        return $this->difference === 1;
    }

    /**
     * Check if node is root
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent === null;
    }

    public function asArray()
    {
        $data = [
            'key' => $this->key,
            'title' => $this->getData()['name'] . ' (' . $this->getData()['total'] . ')',
            'total' => $this->getData()['total'],
            'children' => []
        ];
        foreach ($this->children as $child) {
            $data['children'][] = $child->asArray();
        }

        return $data;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getDifference()
    {
        return $this->difference;
    }

    /**
     * Compare two nodes. Returns -1, 0, 1 depending on if $this node is on the left, second node belongs to $this node,
     * or second node is on the right
     *
     * @author Zura Sekhniashvili <zurasekhniashvili@gmail.com>
     * @param \intermundia\yiicms\components\Node $node
     * @return int
     */
    protected function compare(Node $node)
    {
        if ($this->lft < $node->lft && $this->rgt > $node->rgt) {
            return 0;
        } else if ($this->lft < $node->lft && $this->rgt < $node->rgt) {
            return -1;
        }

        return 1;
    }
}