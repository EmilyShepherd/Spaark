<?php namespace Spaark\Core\Model;
/**
 */


use \Spaark\Core\Error\NoSuchMethodException;


class Session extends Singleton
{
    public static function create()
    {
        if (!isset($_COOKIE['Spaark_SID']))
        {
            return new Session(uniqid());
        }
        else
        {
            try
            {
                return Session::fromId
                (
                    $_COOKIE['Spaark_SID']
                );
            }
            catch (CacheMiss $cm)
            {
                return new Session(uniqid());
            }
        }
    }
    
    public static function get($var)
    {
        return self::getInstance()->getValue($var);
    }
    
    public function __fromId($sid)
    {
        $this->cache->loadVal('session:' . $sid, $this);
    }
    
    protected function __default($val)
    {
        $this->id      = $val;
        $this->cacheid = 'session:' . $val;
        $this->register();
        
        return $this;
    }
    
    public function register()
    {
        setcookie('Spaark_SID', $this->id);
    }
    
    public function save()
    {
        $this->cache->cacheVal($this->cacheid, $this);
    }
}

