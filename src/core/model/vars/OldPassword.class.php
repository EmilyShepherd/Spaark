<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * This class manages the old (simple) password hashing system
 */
class OldPassword extends Variable
{
    /**
     * Hashes the password
     *
     * @param string $hash If provided, the salt from this will be used
     */
    public function hash($hash = NULL)
    {
        if (!$hash)
        {
            $hash = uniqid();
        }

        $salt = substr($hash, 0, 9);

        return $salt . sha1($salt . $this->value . Config::PASSWORD_SALT());
    }

    /**
     * Checks to see if the given hash came from this password
     *
     * @param string $hash The hash to check
     * @return bool Whether the hash came from this password
     */
    public function checkHash($hash)
    {
        return $hash == $this->hash($hash);
    }
}

