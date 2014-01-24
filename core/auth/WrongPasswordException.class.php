<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

require_once FRAMEWORK . 'auth/AuthenticationFailedException.class.php';

/**
 * Thrown when a user cannot be authenticated because the given password
 * was incorrect
 */
class WrongPasswordException extends AuthenticationFailedException
{

}

?>