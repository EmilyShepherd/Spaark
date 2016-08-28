<?php namespace Spaark\Core\Model\Base\Entity;

abstract class UnaryOperator extends Operator
{
    /**
     * @var Operator
     * @readable
     */
    protected $input;

    public function __construct(Operator $op)
    {
        $this->input = $op;
        $this->calculateCost();
    }

    public function size()
    {
        return $this->input->size();
    }
}
