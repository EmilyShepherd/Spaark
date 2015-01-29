<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Thrown when a user attempts to do something before they have been
 * authenticated.
 */
class UnauthorisedException extends SystemException
{
    /**
     * The LoginMethod required to get authorsiation. Defaults to
     * Config::DEFAULT_LOGIN if unset
     */
    private $loginMethod;
    
    /**
     * calls SystemException::__construct() with a generic message. If
     * a $loginMethod is passed, the Spaark authorisation system will
     * attempt to use that to gain Authorisation
     *
     * @param string $loginMethod The string pointer to a method
     * @see SystemException::__construct()
     */
    public function __construct($loginMethod = NULL)
    {
        parent::__construct
        (
            'Unauthorised',
            'You need to login to perform this action'
        );
        
        $this->loginMethod = $loginMethod;
    }
    
    /**
     * Returns the loginMethod required to gain authorisation. If none
     * is set in $loginMethod, Config::DEFAULT_LOGIN is returned.
     *
     * @return string The required LoginMethod
     */
    public function getLoginMethod()
    {
        if ($this->loginMethod)
        {
            return $this->loginMethod;
        }
        else
        {
            return Config::DEFAULT_LOGIN();
        }
    }
}

?>