<?php namespace Spaark\Core\Util\Markup\Grammar;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Collection\Set;


/**
 * Represents a Non Terminal symbol as used in a Grammar
 */
class NonTerminal extends \Spaark\Core\Util\Markup\Symbol
{
    /**
     * The rules which creates this non terminal
     *
     * @var ItemSet<Rule>
     */
    public $rules;

    /**
     * The set of symbols which can follow this non terminal
     *
     * @var Set<Symbol>
     */
    public $following;

    /**
     * The set of non terminals which this non terminal is at the end of
     * 
     * @var Set<Symbol>
     */
    public $endof;

    /**
     * @deprecated
     */
    public $first;

    /**
     * Initialises the NonTerminal
     *
     * @param string $name The name of this element
     */
    public function __construct($name)
    {
        $this->rules     = new ItemSet();
        $this->following = new Set();
        $this->endof     = new Set();
        $this->name      = $name;
    }
    
    /**
     * Add a rule to this symbol
     *
     * This also runs processing on the item
     *
     * @param Rule $rule The rule to add
     */
    public function addToRules($rule)
    {
        $rule->symbol = $this;
        
        $this->rules->add($rule);

        for ($i = 1; $i < count($rule->goesTo); $i++)
        {
            if ($rule->goesTo[$i - 1] instanceof NonTerminal)
            {
                $rule->goesTo[$i - 1]->following->add($rule->goesTo[$i]);
            }
        }

        if ($i && $rule->goesTo[$i - 1] instanceof NonTerminal)
        {
            $rule->goesTo[$i - 1]->endof->add($this);
        }
    }

    /**
     * Returns the set of terminals which may be at the start of this
     * set
     *
     * @getter save
     * @return TokenDescription[] The terminals this can end with
     */
    public function first()
    {
        $without = new Set();
        return $this->_first($without);
    }

    /**
     * */
    public function _first($without)
    {
        $first     = array( );
        $without->add($this);

        foreach ($this->rules as $rule)
        {
            $item = $rule->first();

            if ($without->contains($item))
            {
                continue;
            }
            elseif ($item instanceof NonTerminal)
            {
                $first = array_merge($first, $item->_first($without));
            }
            elseif ($item)
            {
                $first = array_merge($first, array($item));
            }
        }

        return $first;
    }

    /**
     * @getter save
     */
    public function follow()
    {
        $without = new Set();
        return $this->_follow($without);
    }

    public function _follow($without)
    {
        $follow = array( );
        $without->add($this);

        foreach ($this->following as $symbol)
        {
            if ($symbol instanceof NonTerminal)
            {
                $follow = array_merge($follow, $symbol->first());
            }
            else
            {
                $follow[] = $symbol;
            }
        }

        foreach ($this->endof as $symbol)
        {
            if (!$without->contains($symbol))
            {
                $follow = array_merge($follow, $symbol->_follow($without));
            }
        }

        return $follow;
    }
}