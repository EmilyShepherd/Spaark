<?php namespace Spaark\Core\Util\Markup\Grammar\State;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Collection\HashMap;
use Spaark\Core\Util\Markup\Lexical\TokenDescription;
use Spaark\Core\Util\Markup\Grammar\NonTerminal;


/**
 * Represents a state in a LALR(1) Parser
 *
 * @see \Spaark\Core\Util\Markup\Parser
 */
class State extends \Spaark\Core\Model\Base\Composite
{
    /**
     * @internal
     */
    static $i = 0;

    /**
     * The mappings of terminals to Actions
     *
     * @var HashMap<TokenDescription, Action>
     */
    public $action;

    /**
     * The mappings of non terminals to States
     *
     * @var HashMap<NonTerminal, State>
     */
    public $goto;

    /**
     * @internal
     */
    public $id;

    /**
     * @internal
     */
    public $itemset;

    /**
     * Intialises the Hashmaps
     *
     * {@internal This also sets this state's id}}
     */
    public function __construct()
    {
        $this->id     = static::$i++;
        $this->action = new HashMap();
        $this->goto   = new HashMap();
    }

    /**
     * @internal
     */
    public function __debugInfo()
    {
        return array('id' => $this->id);
    }
}