<?php namespace Spaark\Core\Util\Markup\Lexical;
/**
 * Spaark Framework
 *
 * @author Emily Shepherd <emily@emilyshepherd.me>
 * @copyright 2012-2015 Emily Shepherd
 */
defined('SPAARK_PATH') OR die('No direct access');


use Spaark\Core\Model\Base\Comparible;
use Spaark\Core\Util\Markup\Symbol;


/**
 * Contains information about a token, such as its identifier and how
 * to parse it
 */
class TokenDescription extends Symbol
{
    /**
     * @var string
     * @readable
     */
    protected $name;

    /**
     * @readable
     */
    protected $regex;

    /**
     * @var string
     * @readable
     */
    protected $class;

    /**
     * Determines whether or not this token should be displayed within
     * an Abstract Syntax Tree
     *
     * This is used by ASTNodes to determine whether or not they should
     * return the token when it is being iterated over. This is useful
     * for tokens such as brackets which are required in the syntax of
     * the language, but do not add anything to any semantic meaning
     * within the AST.
     *
     * @var boolean
     * @readable
     */
    protected $inAST = true;

    /**
     * Initialises the Token Description with it's name and whether or
     * not it should show up in the abstract syntax tree
     *
     * @param string $name The name of this token
     * @param boolean $ast True if the token should be in an AST
     */
    public function __construct($name, $ast = true)
    {
        $this->name  = $name;
        $this->inAST = $ast;
    }
}