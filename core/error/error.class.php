<?php namespace Spaark\Core\Error;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Handles PHP errors
 */
final class Error extends \Spaark\Core\Base\StaticClass
{
    /**
     * Sets the handlers
     *
     * handler() is used as the error handler
     * fatal() is used as a shutdown function
     *
     * @see Error::handler()
     * @see Error::fatal()
     */
    public static function init()
    {
        set_error_handler
        (
            '\Spaark\Core\Error\Error::handler',
            E_ALL ^ E_STRICT
        );
        register_shutdown_function('\Spaark\Core\Error\Error::fatal');
        
        \Spaark\Core\ClassLoader::load('\Spaark\Core\Error\CaughtException');
    }

    /**
     * Handles all errors by throwing a CaughtException
     *
     * A CaughtException is a general exception that extends
     * SystemException. Its only difference is that CaughtException
     * removes this handler from the stack trace.
     *
     * This method should only be called by PHP
     *
     * @param int $errno The error code - Ignored
     * @param string $errstr The error string - Error message
     * @param string $errfile The file the error occurred in - Ignored
     * @param int $errline The line the error occurred on - Ignored
     * @throws CaughtException Always, passing $errstr to it
     */
    public static function handler($errno, $msg, $file, $line)
    {
        if (strpos($msg, 'require') !== 0)
        {
            throw new CaughtException($msg, $file, $line);
        }
    }
    
    /**
     * Displays an error message
     *
     * Called when the shutdown phase is entered but there isn't any
     * output to use
     *
     * WARNING: Calling this outside of the shutdown phase will cause
     * this message to be cached, if the Output's TTL has been set
     */
    public static function unexpectedEnd()
    {
        
    }
    
    /**
     * Handles fatal errors
     *
     * This is a shutdown function that prints a message if it detects
     * the PHP Engine encountered a fatal error.
     *
     * Calling this yourself will do nothing
     */
    public static function fatal()
    {
        $error  = error_get_last();
        $errors = array(E_ERROR, E_USER_ERROR, E_COMPILE_ERROR);

        if (!$error || !in_array($error['type'], $errors)) return;
        
        @ob_clean();
        @ob_clean();

        //We won't use the templating system in case that is what caused
        //the error.
        \Spaark\Core\View\Page::load
        (
            'spaark_error',
            array
            (
                'message'   => $error['message'],
                'file'      => $error['file'],
                'line'      => $error['line']
            )
        );
        
        exit;
    }
}

?>