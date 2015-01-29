<?php namespace Spaark\Core\Cache;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a string value that lasts for one get
 */
class CacheFlag implements Cacheable, serializable
{
    /**
     * The value of this flag
     */
    private $value;
    
    /**
     * Stores the given value in $this->value for this CacheFlag
     *
     * @param string $value The value of this CacheFlag
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    
    /**
     * Returns $this->value
     *
     * @return string $this->value
     */
    public function __toString()
    {
        return $this->value;
    }
    
    /**
     * Returns true
     */
    public function valid()
    {
        return true;
    }
    
    /**
     * Returns $this->value to be used as the serialized string for this
     * CacheFlag
     *
     * @return string $this->value
     */
    public function serialize()
    {
        return $this->value;
    }
    
    /**
     * Populates $this->value with the given string
     *
     * @param string $str The string
     */
    public function unserialize(string $str)
    {
        $this->value = $str;
    }
}

?>