<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Thrown when a method is caled in an invalid manner.
 *
 * This occurs when the environment can't support the calling of this
 * method
 */
class InvalidMethodCallException extends SystemException
{
    /**
     * Gives the SystemException an error message about the method
     *
     * @param string $method The method that was called
     * @param string $case String explaining when the method can be used
     */
    public function __construct($method, $case)
    {
        parent::__construct
        (
              'You may not call ' . $method . ' in this way. '
            . $method . ' may only be called when ' . $case
        );
    }
}

?>