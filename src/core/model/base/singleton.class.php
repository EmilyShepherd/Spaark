<?php namespace Spaark\Core\Model\Base;

/**
 * Singletons exist only once - each instance that autobuilds it
 * gets the same one
 *
 * This behaviour is not enforced: it's only obeyed by the autoloading
 * functions. This means that you are free to instanciate as many as
 * you want on your own if you want.
 */
class Singleton extends Entity
{
    /**
     * Although this class uses fromSingle() to automatically enforce
     * the singleton rule on the autofactoy methods, getInstance() is
     * more common name
     *
     * @return Singleton The instance of the singleton class
     * @deprecated 
     */
    public static function _getInstance()
    {
        return static::fromSingle();
    }

    /**
     * Calling a from() method with no arguments caches it as "NULL"
     *
     * @return Singleton The instance of the singleton class
     */
    public static function _fromSingle()
    {
        return new static();
    }
}
