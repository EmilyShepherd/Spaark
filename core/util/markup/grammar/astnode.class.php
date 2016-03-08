<?php namespace Spaark\Core\Util\Markup\Grammar;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Base\Composite;
use Spaark\Core\Util\Markup\Lexical\Token;


/**
 * Represents a Node in an Abstract Syntax Tree
 *
 *
 */
class ASTNode extends Composite implements \Iterator
{
    /**
     * The rule which was used to reduce to this ASTNode
     *
     * @var Rule
     */
    public $rule;

    /**
     * The children of this node
     *
     * It is important to note that when iterating over a node, some of
     * these items may not be included, if they are nodes made from
     * transparent rules or tokens which have been labelled as not to
     * appear in an AST
     *
     * @var ASTNode|Token[]
     */
    public $raw;

    /**
     * The current virtual key
     *
     * @var int
     */
    private $pointer = 0;

    /**
     * Each Node may be annotated with relavent meta data by a parser
     *
     * There is no definition of what this is - each parser may use this
     * space as they required
     *
     * @var mixed
     */
    public $annotation;

    /**
     * Initiates the Node with the array of sub items and the rule which
     * this node was reduced with
     *
     * @param ASTNode|Token[] $items The sub items of the node
     * @param Rule $rule The rule used for this node
     */
    public function __construct($items, $rule)
    {
        $this->raw  = $items;
        $this->rule = $rule;
    }

    /**
     * The current sub element which is either obtained from the raw
     * data directly, or by calling current on the current raw item if
     * it is transparent
     *
     * @return ASTNode|Token The current item
     */
    public function current()
    {
        $cur = current($this->raw);

        if (!($cur instanceof ASTNode) || !$cur->rule->transparent)
        {
            return $cur;
        }
        else
        {
            return $cur->current();
        }
    }

    public function rewind()
    {
        $this->pointer = 0;
        $cur = $this->checkCurrent(reset($this->raw));

        if ($cur instanceof ASTNode && $cur->rule->transparent)
        {
            $cur->rewind();

            return $cur->current();
        }

        return $cur;
    }

    public function key()
    {
        return $this->pointer;
    }

    public function valid()
    {
        return !!current($this->raw);
    }

    public function next()
    {
        $this->pointer++;

        $cur = current($this->raw);

        if ($cur instanceof Token || !$cur->rule->transparent)
        {
            $cur = next($this->raw);

            if ($cur instanceof ASTNode && $cur->rule->transparent)
            {
                $cur->rewind();
            }
        }
        elseif ($cur instanceof ASTNode)
        {
            $cur->next();

            if (!$cur->valid())
            {
                $cur = next($this->raw);
            }
        }

        return $this->checkCurrent($cur);
    }

    private function checkCurrent($cur)
    {
        if ($cur instanceof Token && !$cur->description->inAST)
        {
            return $this->next();
        }
        else
        {
            return $cur;
        }
    }

    /**
     * @getter
     */
    public function name()
    {
        if ($this->rule->overridename !== NULL)
        {
            return $this->raw[$this->rule->overridename]->name;
        }
        else
        {
            return $this->rule->symbol;
        }
    }
}