<?php

class Session extends ValueHolder
{
    private static $session;

    public static function getVal($var)
    {
        self::init();
        return self::$session->getValue($var);
    }

    public static function setVal($var, $val)
    {
        self::init();
        self::$session->setValue($var, $val);
    }

    private static function init()
    {
        if (!self::$session)
        {
            self::$session = new Session();
        }
    }

    private function __construct()
    {
        session_start();

        $this->array = $_SESSION;
    }

    public function setValue($var, $val)
    {
        $this->array[$var] = $val;
    }

    public function __destruct()
    {
        foreach ($this->array as $var => $val)
        {
            $_SESSION[$var] = $val;
        }
    }
}

