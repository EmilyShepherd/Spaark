<?php namespace Spaark\Core\Error;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Thrown by Error when it catches a PHP error
 */
class CaughtException extends \Exception
{
    /**
     * Generates the stack trace for this exception
     *
     * It removes Error::handler() from the stack trace and corrects
     * the file and line. Theses actions are requierd to remove
     * the Spaark's handling of the error from the trace, as it is
     * irrelavent
     */
    public function __construct($msg, $file, $line)
    {
        parent::__construct($msg);

        $this->file = $file;
        $this->line = $line;
    }
}

?>