<?php namespace Spaark\Core\Util\Regex;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use \Spaark\Core\Util\Markup\Lexer;
use \Spaark\Core\Util\Markup\Lexical;
use \Spaark\Core\Util\Stream\Stream;


/**
 * Performs lexical analysis for a regular expression
 *
 * Normally this would be done with the Lexer class using regex, however
 * as this class is used in the construction of regexs, in this case it
 * must be done manually.
 */
class RegexLexer extends Lexer
{
    /**
     * Open bracket TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $bo;

    /**
     * Close bracket TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $bc;

    /**
     * Open square bracket TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $sbo;

    /**
     * Close square bracket TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $sbc;

    /**
     * Operator ("?", "*", "+") TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $op;

    /**
     * Or Bar ("|") TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $bar;

    /**
     * Hat ("^") TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $hat;

    /**
     * Dot (".") TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $dot;

    /**
     * Any Character TokenDescription
     *
     * @var Lexical\TokenDescription
     */
    public static $c;

    /**
     * The Token at the head of the lexer
     *
     * @var Lexical\Token
     */
    private $head = NULL;
    
    /**
     * Magic classloader method to initialise the constant objects
     */
    public static function RegexLexer_onload()
    {
        static::$bo   = new Lexical\TokenDescription('(', false);
        static::$bc   = new Lexical\TokenDescription(')', false);
        static::$sbo  = new Lexical\TokenDescription('[', false);
        static::$sbc  = new Lexical\TokenDescription(']', false);
        static::$op   = new Lexical\TokenDescription('+?*', false);
        static::$bar  = new Lexical\TokenDescription('|', false);
        static::$hat  = new Lexical\TokenDescription('^', false);
        static::$dot  = new Lexical\TokenDescription('.');
        static::$c    = new Lexical\TokenDescription('1');
    }

    /**
     * Constructs this Lexer with a stream of characters
     *
     * @param Stream $stream The input stream
     */
    public function __construct(Stream $stream)
    {
        parent::__construct($stream);

        $this->next();
    }
    
    /**
     * Returns the next Token and increments the internal pointer
     *
     * @return Lexical\Token The next Token in the stream
     */
    public function next()
    {
        $char  = $this->stream->next();

        if ($char === '\\')
        {
            return $this->head = new Lexical\Token($this->stream->next(), static::$c);
        }
        else
        {
            return $this->head = new Lexical\Token($char, $this->nextToken($char));
        }
    }

    public function peek()
    {
        return $this->head;
    }
    
    private function nextToken($byte)
    {
        switch ($byte)
        {
            case '(': return static::$bo;
            case ')': return static::$bc;
            case '[': return static::$sbo;
            case ']': return static::$sbc;
            case '|': return static::$bar;
            case '^': return static::$hat;
            case '.': return static::$dot;
            case '+':
            case '?':
            case '*':
                return static::$op;
            case NULL: return Lexer::$EOF;
            default:
                return static::$c;
          
        }
    }

    public function read($count)
    {

    }

    public function seek($pos)
    {

    }
}
