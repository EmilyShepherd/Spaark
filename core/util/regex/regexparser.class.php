<?php namespace Spaark\Core\Util\Regex;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Util\Markup\Parser;
use Spaark\Core\Util\Markup\Grammar\NonTerminal;
use Spaark\Core\Util\Markup\Grammar\Rule;
use Spaark\Core\Util\Markup\Grammar\ASTNode;


/**
 * Parses a Regular Expression string and creates an NFA
 */
class RegexParser extends Parser
{
    /**
     * Constructs a new Parser, using the precoded regular expression
     * grammar
     */
    public function __construct()
    {
        $brackets = new NonTerminal('bracket');
        $char     = new NonTerminal('character');
        $string   = new NonTerminal('string');

        $expr     = new NonTerminal('expr');
        $expr->addToRules(new Rule([RegexLexer::$dot], 0, 'dot'));
        $expr->addToRules(new Rule([RegexLexer::$c], 0, 'char'));
        $expr->addToRules(new Rule([$expr, RegexLexer::$bar, $expr], 6, ['or', 0, 2]));
        $expr->addToRules(new Rule([$expr, $expr], 7, 'and'));
        $expr->addToRules(new Rule([$expr, RegexLexer::$op], 8, [1, 0]));
        $expr->addToRules(new Rule([$brackets], 9));
        $expr->addToRules(new Rule([RegexLexer::$bo, $expr, RegexLexer::$bc], 9));
        
        $brackets->addToRules(new Rule([RegexLexer::$sbo, RegexLexer::$hat, $string, RegexLexer::$sbc]));
        $brackets->addToRules(new Rule([RegexLexer::$sbo, $string, RegexLexer::$sbc]));

        $char->addToRules(new Rule([RegexLexer::$dot]));
        $char->addToRules(new Rule([RegexLexer::$c]));

        $string->addToRules(new Rule([$char, $string], true));
        $string->addToRules(new Rule([$char], true));

        parent::__construct($expr);
    }

    /**
     * Performs a reduction on a non-transparent grammar rule
     *
     * This produces the relavent NFA states for the rule
     *
     * @param ASTNode $node The rule which has just been reduced to
     */
    protected function reduction($node)
    {return;
        // Check what the rule is and do something with it!
        switch ($node->name)
        {
            case 'character':
                $start = new State();
                $end   = new State();
                $start->add($node->raw[0], $end);

                $node->annotation = new Node($start, $end);
                break;

            case '?':
                $node->rewind();
                $sub = $node->current()->annotation;
                $sub->start->add(rand(), $sub->final);

                $node->annotation = new Node($sub->start, $sub->final);
                break;

            case '*':
                $node->rewind();
                $sub = $node->current()->annotation;
                $sub->start->add(rand(), $sub->final);
                $sub->final->add(rand(), $sub->start);

                $node->annotation = new Node($sub->start, $sub->final);
                break;

            case '+':
                $node->rewind();
                $sub = $node->current()->annotation;
                $sub->final->add(rand(), $sub->start);

                $node->annotation = new Node($sub->start, $sub->final);
                break;

            case 'or':
                $node->rewind();
                $LHS = $node->current()->annotation;
                $node->next();
                $RHS = $node->current()->annotation;

                $node->annotation = new Node(new State(), new State());
                $node->annotation->start->add(rand(), $LHS->start);
                $node->annotation->start->add(rand(), $RHS->start);

                $LHS->final->add(rand(), $node->annotation->final);
                $RHS->final->add(rand(), $node->annotation->final);
                break;

            case 'then':
                $node->rewind();
                $LHS = $node->current()->annotation;
                $node->next();
                $RHS = $node->current()->annotation;

                $node->annotation = new Node($LHS->start, $RHS->final);

                $LHS->final->add(NULL, $RHS->start);

                break;

            default:
                echo 'ERROR';
                echo $node->name;
                exit;
        }
    }
}

