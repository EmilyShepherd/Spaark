<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Thrown when in development to provide debug information
 */
class DebugException extends SystemException
{
    /**
     * Uses the debug message provided, or "Debug point reached"
     *
     * @param string $message The debug message
     */
    public function __construct($message = 'Debug point reached')
    {
        parent::__construct
        (
            $message,
            'This page is still under development'
        );
    }
}

?>