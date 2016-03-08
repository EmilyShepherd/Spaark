<?php namespace Spaark\Core\Util\Markup\Lexical;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Base\Composite;


/**
 * Instance of a Token, as found by a Lexer during lexical analysis
 */
class Token extends Composite
{
    /**
     * The general description of the token, such as its identifying
     * name and regular expression
     *
     * @var TokenDescription
     * @readable
     */
    protected $description;
    
    /**
     * The exact string that was matched to form this token
     *
     * @var string
     * @readable
     * @writable
     */
    protected $matched = '';

    /**
     * The line at which this token appeared
     *
     * @var int
     */
    protected $line;

    /**
     * The character within the line at which this token appeared
     *
     * @var int
     */
    protected $character;

    public function __construct($matched, $desc)
    {
        $this->matched     = $matched;

        $this->description = $desc;
    }

    /**
     * Returns the token's name
     *
     * @getter
     * @return string The token's name
     */
    public function name()
    {
        return $this->matched;
    }
}