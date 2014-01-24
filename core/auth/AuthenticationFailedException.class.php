<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Thrown when a user could not be authenticated for some reason
 */
class AuthenticationFailedException extends SystemException
{
    /**
     * Calls SystemException::__construct() with a generic message
     *
     * @see SystemException::__construct()
     */
    public function __construct()
    {
        parent::__construct('The user could not be authenticated');
    }
}

?>