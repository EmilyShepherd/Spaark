<?php namespace Spaark\Core\Base;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Indicates that the class is static. This class does nothing except
 * throw an exception when something attempts to instantiate it.
 */
abstract class StaticClass
{
    /**
     * Throws a SystemException, as static classes cannot be
     * instantiated.
     *
     * @throws SystemException Always
     */
    public function __construct()
    {
        throw new SystemException
        (
            'Attempted to instantiate a static class'
        );
    }
}

?>