<?php namespace Spaark\Core\Util\Regex;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Collection\HashMap;


/**
 * Represents a State in either an NFA or DFA which is used by regular
 * expressions
 */
class State extends \Spaark\Core\Model\Base\Composite
{
    /**
     * The moves from this state to other states
     *
     * @var HashMap
     * @readable
     */
    protected $paths;

    /**
     * Used for debugging
     *
     * @internal
     */
    public $id;

    /**
     * Used for debugging
     *
     * @internal
     */
    static $i = 0;

    /**
     * Whether or not this state is final
     *
     * NB: This is unused in NFAs as their final states are continually
     * changed as they are being built. Each NFA, therefore, is designed
     * to only contain a single final state, which is pointed to in a
     * Regex "Node".
     *
     * @var boolean
     */
    public $final;

    /**
     * Builds the State
     *
     * @param boolean $final Whether or not this state is final
     */
    public function __construct($final = false)
    {
        $this->paths = new HashMap();
        $this->id    = ++static::$i;
        $this->final = $final;
    }

    /**
     * Adds a new move from this state to another
     *
     * NB: If the token is an integer, this is assumed to be an epsilon
     * move
     *
     * @param mixed $token The character to read to make the move
     * @param State $state The state to move to
     */
    public function add($token, $state)
    {
        $this->paths[$token] = $state;
    }
}