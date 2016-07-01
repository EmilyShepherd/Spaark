<?php

class Login
{
    public static function setCurrentUser(AbstractUser $user)
    {
        Session::setVal('currentUser', $user);
        Session::setVal('runAs', $user);
    }

    public static function runAs(AbstractUser $user)
    {
        Session::setVal('runAs', $user);
    }

    public static function getUser()
    {
        return Session::getVal('runAs');
    }

    public static function getCurrentUser()
    {
        return Session::getVal('currentUser');
    }

    public static function hasUser()
    {
        return !!Session::getVal('runAs');
    }
}

