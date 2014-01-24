<?php namespace Spaark\Core\Cache;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Thrown when the requested cache doesn't exist
 */
class CacheMiss extends \Exception
{
    /**
     * Sets the Exception message
     *
     * @param string $bucket The cache bucket
     * @param string $cache The cache
     */
    public function __construct($bucket, $cache)
    {
        parent::__construct
        (
              'The system failed to handle a cache miss for '
            . $bucket . '/' . $cache
        );
    }
}

?>