<?php namespace Spaark\Core\Model;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a path
 */
class Path extends Base\Model
{
    /**
     * Takes a path, either as an array of parts or as a single string
     *
     * @param mixed $arr The path
     */
    public function __construct($arr)
    {
        $this->value = is_array($arr) ? implode('/', $arr) : $arr;
    }

    /**
     * Returns the path
     *
     * @return string The path
     */
    public function __toString()
    {
        return $this->value;
    }
}

?>