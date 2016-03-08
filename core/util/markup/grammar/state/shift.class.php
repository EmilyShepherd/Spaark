<?php namespace Spaark\Core\Util\Markup\Grammar\State;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


/**
 * Indicates that the token should be pushed onto the stack and the
 * state updated
 *
 * @see \Spaark\Core\Util\Markup\Parser
 */
class Shift extends Action
{
    /**
     * The state to move into
     *
     * @var State
     */
    public $state;

    /**
     * Intiliases this action with its state
     *
     * @param State $state The state to move into
     */
    public function __construct($state)
    {
        $this->state = $state;
    }
}