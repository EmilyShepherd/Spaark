<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Thrown when the request cannot be responded to because it is invalid
 *
 * An example of this is a a method that can only respond with AJAX,
 * that is called by a non-ajax request
 */
class InvalidRequestException extends SystemException
{
    /**
     * Gives a message to the SystemException about the request
     *
     * @param string $expectedType The request type this requires
     * @param string $responseType The response type that would have
     *     been used
     */
    public function __construct($expectedType, $responseType = NULL)
    {
        $msg =
              'Invalid Request'                           . "\r\n"
            . 'Expected request type: ' . $expectedType   . '.';

        if ($responseType)
        {
            $msg .=
                                                            "\r\n"
                . 'Normal response type: ' . $responseType . '.';
        }

        parent::__construct($msg);
    }
}

?>