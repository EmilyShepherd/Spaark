<?php namespace Spaark\Core\Model\Collection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lazycollection
 *
 * @author Emily Shepherd
 */
abstract class LazyCollection extends Collection
{
    /**
     * Attempts to build the value at the given position
     *
     * This method is normally called by offsetGet when a value is
     * asked for which does not appear to exist. This method must
     * generate and return the specific value that was asked for,
     * howver it is acceptable for it to generate more items and add
     * these to $data if this is efficient.
     *
     * @param scalar $position The position to build
     * @return mixed The value of that position
     */
    abstract protected function get($position);

    /**
     * Returns the data at the given offset
     *
     * If the offset does not exist, and the lazy collection does not
     * appear to have finished building itself, this method will call
     * the child class to ask it to attempt to build the value.
     *
     * @param scalar $offset The offset to try to get
     * @return mixed The value at the given offset
     * @see get($position)
     */
    public function offsetGet($position)
    {
        if (isset($this->data[$position]))
        {
            return $this->data[$position];
        }
        elseif (count($this->data) < $this->size())
        {
            return $this->data[$position] = $this->get($position);
        }
    }
}
