<?php namespace Spaark\Core\Model\Base\Entity;

use \Spaark\Core\Model\Collection\LazyCollection;

abstract class Operator extends LazyCollection
{
    /**
     * Cost
     *
     * @var float
     * @readable
     */
    protected $cost;

    abstract protected function calculateCost();
}
