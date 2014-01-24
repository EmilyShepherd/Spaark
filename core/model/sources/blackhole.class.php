<?php namespace Spaark\Core\Model\Sources;

/**
 * This class can be used as a placeholder result resource for data
 * sources when the result is known to be empty
 *
 * If a resource is set to an instance of BlackHole, BaseSource will
 * automatically return 0 for count requests etc. Any and all requests
 * made to this class return NULL (allowing your implementations to
 * carry on as if its a real resource).
 */
class BlackHole
{
    /**
     * Returns null for all method calls
     *
     * @param ignored $func Ignored
     * @param ignored $args Ignored
     * @return NULL         Always NULL
     */
    public function __call($func, $args)
    {
        return NULL;
    }

    /**
     * Returns null for all member variables
     *
     * @param ignored $var Ignored
     * @return NULL        Always NULL
     */
    public function __get($var)
    {
        return NULL;
    }
}