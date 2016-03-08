<?php namespace Spaark\Core\Util\Regex;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use \Spaark\Core\Util\Markup\Parser;
use \Spaark\Core\Util\Markup\Grammar\NonTerminal;
use \Spaark\Core\Util\Markup\Grammar\Rule;
use \Spaark\Core\Util\Stream\StringStream;
use \Spaark\Core\Model\Collection\Set;
use \Spaark\Core\Model\Collection\HashMap;


/**
 * Represents a Regular Expression
 *
 */
class Regex
{
    /**
     * The Abstract Syntax Tree which was formed when parsing this
     * regular expression
     *
     * @var \Spaark\Core\Util\Markup\Grammar\ASTNode
     * @access public
     */
    public $ast;

    /**
     * The starting state for this Regular Expression
     *
     * @var State
     * @access public
     */
    public $start;

    /**
     * All states in this Regular Expression's NFA
     *
     * @var \Spaark\Core\Model\Collection\HashMap
     */
    private $allStates;

    /**
     * The final state for this Regular Expression's DFA
     *
     * @var State
     */
    private $final;

    /**
     * Accepts a string regular expression
     *
     * @param string $string The regular expression
     */
    public function __construct($string)
    {
        $this->ast = $this->parse($string);
        //$this->constructDFA();
    }
    
public $temp;

    /**
     * Parses a regex string and produces an AST and NFA
     *
     * @param string $str The regular expression
     * @return \Spaark\Core\Util\Markup\Grammar\ASTNode The AST
     */
    private function parse($str)
    {
        $parser = new RegexParser();
        $this->temp = $parser;
        
        return $parser->parse(new RegexLexer(new StringStream($str)));
    }

    /**
     * Converts the regex's NFA into a DFA
     *
     */
    private function constructDFA()
    {
        $state = new Set();
        $state->add($this->ast->annotation->start);

        $this->allStates = new HashMap();
        $this->final     = $this->ast->annotation->final;
        $this->start     = $this->makeState($state);
    }

    /**
     * Follows the moves from the given state to find the possible
     * state moves
     *
     * This follows each epsilon move and adds that state to the set
     * of possible states, and lists each epislon-move, creating a
     * set of possible states for each of those.
     *
     * @param State $state The state to check
     * @param Set $newStateName The possible states we are in
     * @param HashMap $moves Mapping of each move to a set of states
     */
    private function discoverState($state, $newStateName, $moves)
    {
        if ($newStateName->contains($state)) return;
        
        $newStateName->add($state);

        foreach ($state->paths as $token => $move)
        {
            if (!is_object($token))
            {
                $this->discoverState($move, $newStateName, $moves);
            }
            else
            {
                if (!isset($moves[$token->name]))
                {
                    $moves[$token->name] = new Set();
                }

                $moves[$token->name]->add($move);
            }
        }
    }

    /**
     * Turns a set of possible NFA states into a single state for a DFA
     *
     * If this state is found to already exist, this is returned instead
     * of recreating it.
     *
     * @param Set $set The set of possible NFA states
     * @return State The state for the DFA
     */
    private function makeState($set)
    {
        $newStateName = new Set();
        $moves        = new HashMap();

        foreach ($set as $state)
        {
            $this->discoverState($state, $newStateName, $moves);
        }

        $hash = $newStateName->dataHash();

        if (isset($this->allStates[$hash]))
        {
            return $this->allStates[$hash];
        }

        $newState = $this->allStates[$hash] =
            new State($newStateName->contains($this->final));

        foreach ($moves as $tokenName => $move)
        {
            $newState->add($tokenName, $this->makeState($move));
        }

        return $newState;
    }
}