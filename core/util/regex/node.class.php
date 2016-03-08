<?php namespace Spaark\Core\Util\Regex;

use Spaark\Core\Model\Collection\Set;

class Node extends \Spaark\Core\Model\Base\Composite
{
    /**
     * @var State
     * @readable
     */
    public $start;

    /** 
     * @var State
     * @readable
     */
    protected $final;

    public function __construct($start, $final)
    {
        $this->start = $start ?: new State();
        $this->final = $final;
    }
}