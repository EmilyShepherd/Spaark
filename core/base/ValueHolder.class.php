<?php namespace Spaark\Core\Base;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Stores values in $this->array which are readonly.
 *
 * Values can be accessed as if they were public member variables, for
 * example $obj->array['val'] can be read via $obj->val
 */
class ValueHolder
{
    /**
     * The array of values. Eveything in here can be read (unless the
     * class has a public member variable with the same name)
     */
    protected $array = array( );
    
    /**
     * Returns the value of that item, if it exists in the array.
     *
     * If it is not set, this returns NULL. This can be useful as it
     * allows you to not check isset() whilst maintaining strict
     * compliancy.
     *
     * @param string $item The key for the value in the array to fetch
     * @return mixed The value in the array, or NULL
     */
    public function getValue($item)
    {
        if (isset($this->array[$item]))
        {
            return $this->array[$item];
        }
        else
        {
            return NULL;
        }
    }
    
    /**
     * Allows array items to be read as if member variables of the class
     *
     * @param string $var The variable name
     * @return mixed The value in the array, or NULL
     * @see ValueHolder::getValue()
     */
    public function __get($var)
    {
        return $this->getValue($var);
    }
    
    /**
     * Returns the entire array
     *
     * @return array The entire array
     */
    public function getArray()
    {
        return $this->array;
    }
}

?>