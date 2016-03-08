<?php namespace Spaark\Core\Util\Markup;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use \Spaark\Core\Util\Stream\Stream;


/**
 * The Lexer reads a stream of characters and converts it into a
 * stream of tokens
 *
 * Typically, this token stream would then be passed to a parser.
 *
 * @author Emily Shepherd
 */
class Lexer implements Stream
{
    /**
     * The stream of chatacters that this Lexer is converting
     *
     * @var Stream
     */
    protected $stream;
    
    /**
     * 
     */
    private $tokens = array( );
    
    private $reg;

    /**
     * A special Token representing the end of the stream (dubbed EOF,
     * for End of File)
     *
     * @var Lexical\TokenDesctription
     */
    public static $EOF;

    /**
     * Magic ClassLoader method which initialises the static $EOF
     */
    public static function Lexer_onload()
    {
        static::$EOF = new Lexical\TokenDescription('EOF', false);
    }
    
    /**
     * Creates a tokeniser from the given stream
     */
    public function __construct(Stream $stream)
    {
        $this->stream = $stream;
    }
    
    public function peek()
    {
        
    }

    public function next()
    {

    }

    public function read($bytes)
    {

    }

    public function seek($pos)
    {

    }
}