<?php namespace Spaark\Core\Util\Markup\Grammar;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Collection\Collection;


/**
 * Stores a set of grammar rules
 *
 * @see Rule
 */
class ItemSet extends Collection
{
    /**
     * Max Priority
     *
     * @var int
     * @readable
     */
    public $priority = 0;

    /**
     * Returns the set of all rules we may be in
     *
     * For example, for the grammar:
     *   + S -> E
     *   + S -> n
     *   + E -> ( S )
     *   + E -> S + T
     *   + T -> 0
     *   + T -> 1
     *
     * For the ItemSet:
     *   + S -> .E
     *   + S -> .n
     *
     * The closure would become:
     *   + S -> .E
     *   + S -> .n
     *   + E -> .( E )
     *   + E -> .S + T
     *
     * @getter save
     * @return Rule[] The closure of the set
     */
    public function closure()
    {
        $without = array( );
        return $this->_closure($without);
    }

    public function _closure(&$without)
    {
        $closure = array( );

        foreach ($this->data as $item)
        {
            $closure[] = $item;

            $symbol    = $item->first();

            if
            (
                $symbol &&
                $symbol instanceof NonTerminal &&
                !in_array($symbol, $without)
            )
            {
                $without[] = (string)$symbol;

                foreach ($symbol->rules->_closure($without) as $rule)
                {
                    $closure[] = $rule;
                }
            }
            elseif (!in_array($symbol, $without))
            {
                $without[] =(string)$symbol;
            }
        }

        return $closure;
    }

    public function add($item)
    {
        parent::add($item);

        $this->priority = max($item->priority, $this->priority);
    }
}