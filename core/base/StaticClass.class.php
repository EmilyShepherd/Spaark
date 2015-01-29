<?php namespace Spaark\Core\Base;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

// {{{ Exceptions
    
    /**
     * This exception is thrown whenever a static class is instantiated 
     */
    class CannotInvokeStaticClassException extends \Exception
    {
        public function __construct($class)
        {
            parent::__construct
            (
                'Cannot instantiate static class: ' . $class
            );
        }
    }
    
    // }}}
    
        ////////////////////////////////////////////////////////

/**
 * Indicates that the class is static. This class does nothing except
 * throw an exception when something attempts to instantiate it.
 */
abstract class StaticClass
{
    /**
     * Throws a CannotInvokeStaticClassException, as static classes
     * cannot be instantiated.
     *
     * @throws CannotInvokeStaticClassException Always
     */
    public final function __construct()
    {
        throw new CannotInvokeStaticClassException(get_called_class());
    }
}

?>