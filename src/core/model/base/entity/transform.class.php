<?php namespace Spaark\Core\Model\Base\Entity;

class Transform extends UnaryOperator
{
    /**
     * Attribute
     *
     * @var Attribute
     * @readable
     */
    protected $from;

    protected $to;

    public function __construct($input, $from, $to)
    {
        $this->from = $from;
        $this->to   = $to;

        parent::__construct($input);
    }

    public function get($pos)
    {
        if (!($next = $this->input[$pos])) return NULL;

        $next[$this->to] = $next[$this->from];
        unset($next[$this->from]);

        return $next;
    }

    protected function calculateCost()
    {
        //
    }
}
