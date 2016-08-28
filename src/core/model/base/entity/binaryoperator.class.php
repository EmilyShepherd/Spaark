<?php namespace Spaark\Core\Model\Base\Entity;

abstract class BinaryOperator extends Operator
{
    /**
     * @var Operator
     * @readable
     */
    protected $left;

    /**
     * @var Operator
     * @readable
     */
    protected $right;

    public function __construct(Operator $left, Operator $right)
    {
        $this->left  = $left;
        $this->right = $right;
        $this->calculateCost();
    }
} 
