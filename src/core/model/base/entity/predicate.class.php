<?php namespace Spaark\Core\Model\Base\Entity;

class Predicate extends \Spaark\Core\Model\Base\Composite
{
    /**
     * Attribute
     *
     * @var Predicate
     * @readable
     */
    protected $left;

    /**
     * Operand
     *
     * @var string
     * @readable
     */
    protected $operand;

    /**
     * Right
     *
     * @var Predicate
     * @readable
     */
    protected $right;

    public function __construct($left, $op, $right)
    {
        $this->left    = $left;
        $this->operand = $op;
        $this->right   = $right;
    }

    public function match($line)
    {
        return eval('return '
            . '\'' . $this->left->match($line)  . '\''
            . $this->operand
            . '\'' . $this->right->match($line) . '\';'
        );
    }
}
