<?php namespace Spaark\Core\Util\Markup;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Collection\HashMap;
use Spaark\Core\Model\Collection\Stack;
use \Spaark\Core\Util\Stream\Stream;


/**
 * Represents a Bottom-Up LALR(1) Parser
 *
 * This can used both to convert a given grammar into a set of LALR(1)
 * parser states, and to run a parsing loop using such states on a
 * stream of Tokens.
 */
class Parser
{
    /**
     * The grammar rules of this parser
     *
     * @var NonTerminal
     */
    protected $grammar;
    
    /**
     * The set of parser states for this grammar
     *
     * @var Grammar\State\State[]
     */
    public $states = array( );

    /**
     * The initial state for this parser
     *
     * @var Grammar\State\State
     */
    public $startState;

    protected $state;

    protected $stateStack;

    protected $tokenStack;

    protected $output;
    
    /**
     * Builds a set of parser states from a given grammar
     *
     * @param Grammar\NonTerminal $grammar The grammatical rules for
     *     this parser
     */
    public function __construct($grammar)
    {
        $this->grammar = $grammar;

        $this->buildTable();
    }

    /**
     * Parses the given stream of tokens using this parsers internal
     * state table
     *
     * @param Stream $lexer The stream of tokens to parse
     * @return Grammar\ASTNode The parsed Abstract Syntax Tree
     */
    public function parse(Stream $lexer)
    {
        $state      = $this->startState;
        $stateStack = new Stack();
        $tokenStack = new Stack();

        // Main Parser Loop
        while (1)
        {
            $LA = $lexer->peek();

            // Loop over the possible actions for our current state
            foreach ($state->action as $token => $action)
            {
                // Check if the current LA symbol matches our action's
                // token
                if ($LA->description === $token)
                {
                    // Pushes the current state and LA onto the stack
                    // before shifting into the action's specified new
                    // state
                    if ($action instanceof Grammar\State\Shift)
                    {
                        $lexer->next();

                        $stateStack->push($state);
                        $tokenStack->push($LA);

                        $state = $action->state;
                    }
                    // Removes the relavent items from the token stack
                    // and their associated states. These tokens are
                    // placed into a new AST node which is pushed back
                    // onto the stack before following the relavent
                    // GOTO for the stack at the head of the stack.
                    elseif ($action instanceof Grammar\State\Reduce)
                    {
                        $count = count($action->rule->goesTo);
                        $items = $tokenStack->popMultiple($count);
                        $node  = new Grammar\ASTNode($items, $action->rule);

                        if (!$action->rule->transparent)
                        {
                            $this->reduction($node);
                        }

                        $tokenStack->push($node);

                        $stateStack->popMultiple($count - 1);

                        $state = $stateStack->peek()
                            ->goto[$action->rule->symbol];
                    }
                    elseif ($action instanceof Grammar\State\Accept)
                    {
                        return $tokenStack[0];
                    }
                    // Incorrect parsing table
                    else
                    {
                        echo 'err';
                    }

                    continue 2;
                }
            }

            // This is reached if no possibilities matched in the loop
            // above so we must raise an error condition.
            echo 'ERR, Unexpected: ' . $LA->name;
            exit;
        }
    }

    /**
     * Called when a non transparent rule is reduced
     *
     * This method does nothing in this class; its existance is so
     * extending classes my override it with their own actions
     *
     * @param Grammar\ASTNode $node The newly reduced node
     */
    protected function reduction($node)
    {
        
    }
    
    /**
     * Builds a table of LALR(1) states from the parser's grammar
     */
    public function buildTable()
    {      
        $g = new Grammar\NonTerminal('S');
        $g->addToRules(new Grammar\Rule([$this->grammar, Lexer::$EOF]));
        $this->startState = $this->createState($g->rules);
    }

    /**
     * Creates a new state from the set of given itemset
     *
     * @param Grammar\ItemSet $itemset The itemset to create a state on
     * @return Grammar\State\State The created state
     */
    private function createState($itemset)
    {
        $state      = new Grammar\State\State();
        $state->itemset = $itemset;
        $itemset    = $itemset->closure();
        $nextstates = new HashMap();
        $setID      = '';

        // Inspect each rule in the itemset
        foreach ($itemset as $item)
        {
            $setID .= $item->setID();

            // If there are no more elements in the rule, this must mean
            // we're at the end of it, so this requies a reduce action
            // for any symbol that can follow this rule
            if (!$item->first())
            {
                foreach ($item->symbol->follow as $symbol)
                {
                    $state->action[$symbol] =
                        new Grammar\State\Reduce($item);
                }
            }
            // If there is a symbol to be read in the following
            // rules, we should save it as a possible next state for
            // this symbol.
            else
            {
                if (isset($nextstates[$item->first()]))
                {
                    $subset = $nextstates[$item->first()];
                }
                else
                {
                    $subset = $nextstates[$item->first()] =
                        new Grammar\ItemSet();
                }

                // The newset is a copy of the rule which its pointer
                // incremented, to indicate that we're in a state in
                // which another token has been read.
                $newset = clone $item;
                $newset->pointer++;

                $subset->add($newset);
            }
        }

        // Now we must check if this set already exists
        if (isset($this->states[$setID]))
        {
            return $this->states[$setID];
        }
        else
        {
            $this->states[$setID] = $state;
        }

        // Loop over the saved states and create states for them
        foreach ($nextstates as $symbol => $item)
        {
            if (isset($state->action[$symbol]))
            {
                if ($state->action[$symbol]->rule->priority >= $item->priority)
                {
                    continue;
                }
            }

            if ($symbol === Lexer::$EOF)
            {
                $state->action[$symbol] = new Grammar\State\Accept();
            }
            elseif ($symbol instanceof Grammar\NonTerminal)
            {
                $state->goto[$symbol] = $this->createState($item);
            }
            else
            {
                $state->action[$symbol] =
                    new Grammar\State\Shift($this->createState($item));
            }
        }

        return $state;
    }
}

