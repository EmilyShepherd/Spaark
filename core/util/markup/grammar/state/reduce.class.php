<?php namespace Spaark\Core\Util\Markup\Grammar\State;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


/**
 * Indicates that input should be reduced
 *
 * @see \Spaark\Core\Util\Markup\Parser
 */
class Reduce extends Action
{
    /**
     * The rule to reduce to
     *
     * @var Rule
     * @readable
     */
    public $rule;

    /**
     * Initiliases the action with its rule
     *
     * @param Rule $rule The rule to reduce to
     */
    public function __construct($rule)
    {
        $this->rule = $rule;
    }
}