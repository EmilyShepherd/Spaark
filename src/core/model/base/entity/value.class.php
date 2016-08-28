<?php namespace Spaark\Core\Model\Base\Entity;

class Value extends Predicate
{
    /**
     * Value
     *
     * @var mixed
     * @readable
     */
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function match($line)
    {
        return $this->value;
    }
} 
