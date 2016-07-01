<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a required authorisation, as saved by an auth string
 */
class Auth
{
    /**
     * Does this auth require a user to be logged on? Default is yes
     */
    private $requireUser = true;

    /**
     * If the user is one of these people, it will pass
     */
    private $userList    = array( );

    /**
     * If the user is a memeber of one of these people, it will pass
     */
    private $groupList   = array( );

    /**
     * Takes a string and calculates its parts
     */
    public function __construct($string = NULL)
    {
        if ($string == 'none')
        {
            $this->requireUser = false;
        }
        else
        {
            $auth    = explode(' ', strtolower($string));

            foreach ($auth as $part)
            {
                if ($part == 'g' || $part == 'group')
                {
                    $last = 'g';
                }
                elseif ($part == 'u' || $part == 'user')
                {
                    $last = 'u';
                }
                elseif ($part)
                {
                    if ($last == 'u')
                    {
                        $this->userList[]  = $part;
                    }
                    elseif ($last == 'g')
                    {
                        $this->groupList[] = $part;
                    }
                }
            }
        }
    }

    /**
     * Returns true if the given user meets the requirements
     *
     * @param mixed $user A user instance or RUN_AS / CURRENT_USER
     * @return bool True if passes, false otherwise
     */
    public function check($user = RUN_AS)
    {
        if (!$this->requireUser) return true;

        if (!Login::hasUser()) return false;

        if ($user === RUN_AS)
        {
            $user = Login::getUser();
        }
        elseif ($user == CURRENT_USER)
        {
            $user = Login::getCurrentUser();
        }
        elseif (!is_a($user, 'AbstractUser'))
        {
            throw new SystemException
            (
                  'Passed $user should be RUN_AS, CURRENT_USER '
                . 'or an instance of an AbstractUser'
            );
        }

        if
        (
            (empty($this->userList) && empty($this->groupList)) ||
            $this->pass('userList',  'is',       $user)         ||
            $this->pass('groupList', 'memberOf', $user)
        )
        {
            return true;
        }

        return false;
    }

    /**
     * Checks for each member of the given $arr, if $user->$method($i)
     * evaluates to true. If any of them do, it returns true, otherwise
     * false
     *
     * @param array $arr The arry to iterate over
     * @param string $method The method to call
     * @param AbstractUser $user The user to call the $method on
     * @return bool True if any $method calls return true
     */
    private function pass($arr, $method, $user)
    {
        if (!empty($this->$arr))
        {
            foreach ($this->$arr as $id)
            {
                if ($user->$method($id))
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Uses check() to see if the user passes, throws an
     * UnauthorisedException.
     *
     * @param mixed $user A user instance or RUN_AS / CURRENT_USER
     * @throws UnauthorisedException if the check fails
     * @see check()
     */
    public function requirePass($user = RUN_AS)
    {
        if (!$this->check($user))
        {
            throw new UnauthorisedException();
        }
    }
}

