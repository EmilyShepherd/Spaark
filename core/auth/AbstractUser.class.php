<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a User that Spaark can interact with
 */
interface AbstractUser
{
    /**
     * Returns the Permission
     *
     * @param int $pid The id of the Permission to get
     * @param bool $throw If true, it will throw an Exception
     * @return Permission The Permission
     */
    public function getPermission($pid, $throw);
    
    /**
     * 
     */
    public function isInGroup($gid, $throw);
}

?>