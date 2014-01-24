<?php namespace Spaark\Core\Cache;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Classes that implement this can be saved in a Cache object
 */
interface Cacheable extends \serializable
{
    /**
     * Returns whether or not this Cacheable object is valid to be used.
     * Implemented methods should return false when Cache::NO_CACHE() is
     * true.
     *
     * @return bool True if this Cachable object is valid
     */
    public function valid();
    
    /**
     * Returns whether or not this Cacheable object has been changed
     * since being loaded. The Cache calls this when a Cacheable object
     * is to be saved, to check if it actually needs to write it to disk
     *
     * @return bool True if this Cacheable object is dirty
     */
    public function dirty();
}

?>