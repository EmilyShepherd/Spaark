<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

require_once FRAMEWORK . 'auth/AbstractUser.class.php';

/**
 * Represents a user who's permissions and settings are stored in a
 * database
 */
class SQLUser extends model implements AbstractUser
{
    protected $vars = array
    (
        'uid'       => array(GET => true),
        'groups'    => array(GET => true),
        'hash'      => array(GET => true),
        'initiated' => array(GET => true)
    );
    
    /**
     * Set when the user is correctly found in the database
     */
    protected $initiated = false;
    
    /**
     * The MYSQL associative array from the database about
     * the user
     */
    protected $info;
    
    /**
     * The first part of the MYSQL query to check the users
     * groups
     */
    protected $mysql_query;
    
    /**
     * The user's uid
     */
    protected $uid = false;
    
    /**
     * The user's password hash
     */
    protected $hash;
    
    /**
     * The user's groups
     */
    protected $groups = array( );
    
    /**
     * This array contains options that have already been
     * looked up in the database and have been saved for
     * speed purposes
     */
    protected $options = array( );
    
    /**
     * Contructor function
     *
     * @param array $info The user's info
     * @param $groups The user's groups
     * @return bool True on success, false on failure
     */
    public function __construct($info, $groups = array( ))
    {
        if ($this->initiated)
        {
            return false;
        }
        
        //Start the MySQL query
        $mysql_query =
              'SELECT * from `options` '
            . 'WHERE (`owner`=\'GLOBAL\'';
        
        //Loop through groups
        foreach ($groups as $group)
        {
            if (is_a($group, 'Group'))
            {
                $mysql_query .= ' OR `owner`=\'g:' . $group->gid . '\'';
                $this->groups[] = $group;
            }
            else
            {
                throw new InvalidInputException();
            }
        }
        
        $mysql_query .=
              ') '
            . 'AND `pid`=?';
        
        $this->info = $info;
        
        $this->initiated = true;
        return true;
    }
    
    /**
     * Checks to see if the user is allowed to do something. It saves
     * the result to the SESSION to prevent repeat MYSQL lookups.
     *
     * @param string $option The option that needs checking
     * @param bool $default What to return if no value was found
     * @return mixed 1 if found to be allowed
     *               0 if found to be disallowed
     *               $default if unfound
     */
    public function option($option, $default = false)
    {
        //Check Saved setting        
        if (isset($this->options[$option]))
        {
            return $this->options[$option];
        }
        
        //Check user setting
        $user_test = $this->userOption($option);
        if ($user_test !== false)
        {
            $this->options[$option] = $user_test;
            return $user_test;
        }
        
        //Check group / GLOBAL setting
        $group_test = $this->groupsOption($option);
        if ($group_test !== false)
        {
            $this->options[$option] = $group_test;
            return $group_test;
        }
        
        //If the the default is true then we need to check for entries
        //that deny that option before we assume it's allowed
        if ($default === true)
        {
            $group_test = mysql_query
            (
                  $this->mysql_query . " "
                . "AND `allowed`=0 "
                . "AND `option`='"
                .   mysql_real_escape_string($option) . "'"
            );
        
            if (mysql_num_rows($group_test) != 0)
            {
                $this->options[$option] = 0;
                return 0;
            }
        }
        
        //Every check has yeilded nothing.
        //Return $default
        $this->options[$option] = $default;
        return $default;
    }
    
    /**
     * Gets if the user is allowed to do something, ignoring its
     * group / GLOBAL settings
     *
     * @param string $option The option that needs checking
     * @return mixed 1 if found to be allowed
     *               0 if found to be disallowed
     *               False if unfound
     */
    public function userOption($option)
    {
        $user_test = mysql_query
        (
              "SELECT * from `" . SQL_PREFIX . "options` "
            . "WHERE `owner`='"
            .   "u:" . $this->uid . "' "
            . "AND `option`='"
            .   mysql_real_escape_string($option) . "' "
            . "ORDER BY `allowed` DESC"
        );
        
        if (mysql_num_rows($user_test) == 1)
        {
            $user_test = mysql_fetch_assoc($user_test);
            
            return (int)$user_test['allowed'];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Checks the user's group / GLOBAL settings to see it can do
     * something.
     *
     * @param string $option The option that needs checking
     * @return mixed 1 if found to be allowed
     *               0 if found to be disallowed
     *               False if unfound
     */
    public function groupsOption($option)
    {
        $group_test = mysql_query
        (
              $this->mysql_query . " "
            . "AND `allowed` NOT LIKE 0 "
            . "AND `option`='"
            .   mysql_real_escape_string($option) . "' "
            . "ORDER BY `allowed` DESC"
        );
    
        if (mysql_num_rows($group_test) != 0)
        {
            $group_test = mysql_fetch_assoc($group_test);
            return (int)$group_test['allowed'];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Checks a specific group for a permission
     *
     * @param string $option The option that needs checking
     * @return mixed 1 if found to be allowed
     *               0 if found to be disallowed
     *               False if unfound
     */
    public function groupOption($option, $group)
    {
        $group_test = mysql_query
        (
              "SELECT * from `" . SQL_PREFIX . "options` "
            . "WHERE `owner`='g:" . mysql_real_escape_string($group->getGID()) . "' "
            . "AND `allowed` NOT LIKE 0 "
            . "AND `option`='"
            .   mysql_real_escape_string($option) . "'"
        );
    
        if (mysql_num_rows($group_test) != 0)
        {
            $group_test = mysql_fetch_assoc($group_test);
            return (int)$group_test['allowed'];
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Overrides the database
     *
     * @param string $option The option to override
     * @param mixed $new If passed, this will replace the old value.
     *                   If not, the option will be unset
     */
    public function flushOption($option, $new = NULL)
    {
        if (!$new)
        {
            unset($this->options[$option]);
        }
        else
        {
            $this->options[$option] = $new;
        }
    }
    
    /**
     * flushOptions
     *
     * Empties the User's option array
     */
    public function flushOptions()
    {
        $this->options = array( );
    }
    
    public function getPermission($pid, $throw)
    {
        //
    }
    
    public function isInGroup($gid, $throw)
    {
        return false;
    }
}

?>