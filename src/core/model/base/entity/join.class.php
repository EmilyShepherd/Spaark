<?php namespace Spaark\Core\Model\Base\Entity;

class Join extends BinaryOperator
{
    /**
     * Predicate
     *
     * @var Predicate
     * @readable
     */
    protected $predicate;

    public function __construct($left, $right, $predicate)
    {
        $this->predicate = $predicate;
        parent::__construct($left, $right);
    }

    public function get($pos)
    {
        if (!($next = $this->left[$pos])) return NULL;

        foreach ($this->right as $right)
        {
            $row = array_merge($next, $right);

            if ($this->predicate->match($row))
            {
                return $row;
            }    
        }
    }

    public function size()
    {
        return $this->left->size();
    }

    public function calculateCost()
    {
        //
    }
} 
