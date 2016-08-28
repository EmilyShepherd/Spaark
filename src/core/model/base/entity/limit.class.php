<?php namespace Spaark\Core\Model\Base\Entity;

class Limit extends UnaryOperator
{
    /**
     * Length
     *
     * @var int
     * @readable
     */
    protected $length;

    /**
     * Start
     *
     * @var int
     * @readable
     */
    protected $start = 0;

    public function __construct($input, $length, $start = 0)
    {
        $this->length = $length;
        $this->start  = $start;
        parent::__construct($input);
    }

    public function get($pos)
    {
        if
        (
            $pos >= $this->length
        )
        {
            return NULL;
        }
        else
        {
            return $this->input[$this->start + $pos];
        }
    }

    public function calculateCost()
    {
        //
    }
} 
