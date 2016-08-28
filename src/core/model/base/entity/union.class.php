<?php namespace Spaark\Core\Model\Base\Entity;

class Union extends BinaryOperator
{
    public function __construct($left, $right)
    {
        parent::__construct($left, $right);
    }

    public function get($pos)
    {
        return $this->left[$pos]
            ?: $this->right[$pos - $this->left->size()];
    }

    public function calculateCost()
    {
        //
    }

    public function size()
    {
        return $this->left->size() + $this->right->size();
    }
} 
