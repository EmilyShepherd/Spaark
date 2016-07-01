<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a group that users can be a member of
 */
class Group extends BaseObj
{
    /**
     * If true, __construct cannot be called again
     */
    private $initiated = false;

    /**
     * These are the member variables that can be accessed publically
     */
    protected $vars = array
    (
        'gid'    => array(BaseObj::GET => true),
        'name' => array(BaseObj::GET => true),
        'description'   => array(BaseObj::GET => true)
    );

    /**
     * This group's id
     */
    protected $gid;

    /**
     * This group's name
     */
    protected $name;

    /**
     * This group's description
     */
    protected $description;

    /**
     * Grabs this group from the database and saves it's info
     */
    public function __construct($gid)
    {
        if ($this->initiated)
        {
            return false;
        }

        $this->gid = $gid;

        $group = mysql_query(
              'SELECT * from `' . SQL_PREFIX . 'groups` '
            . 'WHERE `gid`=\'' . mysql_real_escape_string($gid) . '\''
        );
        if (mysql_num_rows($group) == 1)
        {
            $group = mysql_fetch_assoc($group);

            $this->name        = $group['name'];
            $this->description = $group['description'];
        }
    }
}

