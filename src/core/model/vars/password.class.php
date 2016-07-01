<?php namespace Spaark\Core\Model\Vars;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Config\Config;

/**
 * Represents a password
 */
class Password extends HashedValue
{
    const INPUT_TYPE = 'password';

    /**
     * Sets the HashedValue's application salt as Config::PASSWORD_HASH
     *
     * @param string $password The password
     */
    public function __construct($password)
    {
        parent::__construct($password);

        $this->appSalt = Config::PASSWORD_HASH() ?: $this->appSalt;
    }
}


