<?php namespace Spaark\Core\Util\Markup\Grammar;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use \Spaark\Core\Util\Markup\Symbol;


/**
 * Represents a grammatical rule as used by a parser
 */
class Rule extends \Spaark\Core\Model\Base\Composite
{
    /**
     * The list of Symbols this symbol goes to
     *
     * @var Symbol[]
     */
    public $goesTo = array( );

    /**
     * The symbol this rule is for
     *
     * @var Symbol
     */
    public $symbol;

    /**
     * The pointer in the rule we're looking at
     *
     * @var int
     */
    public $pointer = 0;

    /**
     * Indicates whether or not this rule should create a tree entry in
     * the returned Abstract Syntax Tree
     *
     * @var int
     * @readable
     */
    protected $priority = 0;

    /**
     * @var int
     * @readable
     */
    protected $overridename = false;
    
    /**
     * Intialises the rule
     *
     * @param Symbol[] $goesTo The list of symbols this rule goes to
     * @param boolean $transparent If rule is transparent
     * @param int|NULL 
     */
    public function __construct($goesTo, $priority = 0, $overridename = NULL)
    {
        $this->goesTo       = $goesTo;
        $this->priority     = $priority;
        $this->overridename = $overridename;
    }

    /**
     * Returns the first element in this rule
     *
     * @return Symbol|NULL The first element in the rule
     * @getter
     */
    public function first()
    {
        return isset($this->goesTo[$this->pointer])
            ? $this->goesTo[$this->pointer]
            : NULL;
    }

    /**
     * Returns the set ID
     *
     * @return string The set ID
     * @getter save
     */
    public function setID()
    {
        $id = $this->pointer;

        foreach ($this->goesTo as $item)
        {
            $id .= spl_object_hash($item);
        }

        return $id;
    }

    /**
     * Returns a string represntation of the rule in a BNF form
     *
     * @return string The string representation of the rule
     */
    public function __toString()
    {
        $str = $this->symbol . ' -> ';

        // Add each of the symbols to the string with a dot in the
        // place of the current pointer
        for ($i = 0; $i < count($this->goesTo); $i++)
        {
            if ($this->pointer === $i)
            {
                $str .= '.';
            }

            $str .= $this->goesTo[$i];
        }

        // This happens when the pointer is at the end of the rule, so
        // we need to append the dot here
        if ($this->pointer === $i)
        {
            $str .= '.';
        }

        return $str;
    }
}