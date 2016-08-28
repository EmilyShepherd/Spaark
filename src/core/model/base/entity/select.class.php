<?php namespace Spaark\Core\Model\Base\Entity;

class Select extends UnaryOperator
{
    /**
     * Predicate
     *
     * @var Predicate
     * @readable
     */
    protected $predicate;

    public function __construct($input, $predicate)
    {
        $this->predicate = $predicate;
        parent::__construct($input);
    }

    public function get($pos)
    {
        $this->input->seek($pos - 1);

        while ($next = $this->input->next())
        {
            if ($this->predicate->match($next))
            {
                return $next;
            }
        }
    }

    public function calculateCost()
    {
        //
    }
} 
