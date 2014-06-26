<?php namespace Spaark\Core\Model;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


class CacheMiss extends CannotCreateModelException
{
    public function __construct($model, $cid)
    {
        parent::__construct($model, 'Cache', $cid);
    }
}

class CacheCorruptedException extends CacheMiss
{
    public function __construct($key, $obj, $class)
    {
        \Exception::__construct
        (
              'Cache invalid (' . $key . ') '
            . 'loaded ' . get_class($obj) . ' '
            . 'was not expected ' . $class
        );
    }
}


class Cache extends Base\Singleton
{
    public function cacheVal($key, $obj, $ttl = 0)
    {
        $string = $obj->serialize();
        apc_store($key, get_class($obj) . '#' . $string);
    }
    
    public function loadVal($key, $obj = null)
    {
        if (!apc_exists($key)) throw new CacheMiss($obj, $key);
        
        $val   = apc_fetch($key);
        $pos   = strpos($val, '#');
        $class = substr($val, 0, $pos);
        $data  = substr($val, $pos + 1);
        
        if (!$obj)
        {
            $obj = new $class();
        }
        elseif (get_class($obj) != $class)
        {
            throw new CacheCorruptedException($key, $obj, $class);
        }
        
        return $obj->unserialize($data);
    }
}

