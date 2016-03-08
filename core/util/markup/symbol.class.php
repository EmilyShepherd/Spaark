<?php namespace Spaark\Core\Util\Markup;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


/**
 * Represents a symbol, ie either a terminal or non terminal, in a
 * parser grammar
 *
 * This class is never actually directly used - currently it is extended
 * by Grammar\NonTerminal and Lexical\TokenDescription so exists for
 * a neat type hierachy but nothing more.
 */
class Symbol extends \Spaark\Core\Model\Base\Composite
{
    /**
     * @var string
     * @readable
     */
    protected $name;

    /**
     * Returns the name of this symbol
     *
     * @return string The symbol's name
     */
    public function __toString()
    {
        return $this->name;
    }
}