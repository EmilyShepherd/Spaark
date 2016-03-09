<?php namespace Spaark\Core\Cache;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Config\Config;
use \Spaark\Core\Error\CaughtException;

/// {{{ constants

/**
 * TTL of 0 means indefinite
 */
define('INDEFINITE', 0);

define('IN_APC', 0);

define('REQUESTS', 1);

/// }}}
debug_print_backtrace();exit;__halt_compiler();

        ////////////////////////////////////////////////////////


// {{{ Cache

/**
 * Represents a group of cached objects, each with indepentant validity
 * critea. They are used for a common task. Typically to forfil a
 * request.
 */
class Cache extends \Spaark\Core\Model\Base\Entity
{
    private static $buckets  = array( );
    private static $requestBucket;

    /**
     * Creates a cache object for the given URI and stores it as
     * self::$obj
     *
     * @param string $uri The URI of the request Cache to fetch
     */
    public static function init($bucket)
    {
        self::$requestBucket = $bucket;

        register_shutdown_function('\Spaark\Core\Cache\Cache::finalise');
    }

    public static function __callStatic($cache, $args)
    {
        if (!isset($args[0]))
        {
            return self::load($cache);
        }
        else
        {
            self::save($cache, $args[0]);
        }
    }

    public static function load($cache, $bucket = NULL)
    {
        self::initBucket($bucket);

        try
        {
            $return = self::_load($bucket, $cache);

            if (is_string($return))
            {
                $return = self::$buckets[$bucket][$cache] =
                    unserialize($return);
            }

            if (!$return)
            {
                throw new CacheMiss($bucket, $cache);
            }
            elseif (!$return->valid())
            {
                unset(self::$buckets[$bucket][$cache]);
                throw new CacheMiss($bucket, $cache);
            }
            else
            {
                return $return;
            }
        }
        catch (CaughtException $e)
        {
            self::deleteItem($bucket, $cache);
            self::deleteBucket($bucket);

            throw new CacheMiss($bucket, $cache);
        }
    }

    private static function _load($bucket, $cache)
    {
        //Try local memory
        if (isset(self::$buckets[$bucket][$cache]))
        {
            return self::$buckets[$bucket][$cache];
        }

        //Try the APC cache
        $apc_name = Config::CACHE_ID() . ':' . $bucket . '/' . $cache;
        if (apc_exists($apc_name))
        {
            return
                self::$buckets[$bucket][$cache] = apc_fetch($apc_name);
        }

        //Try the filesystem cache
        if (self::loadBucket($bucket))
        {
            return isset(self::$buckets[$bucket][$cache])
                ? self::$buckets[$bucket][$cache]
                : NULL;
        }

        //Fail - return null
        return NULL;
    }

    public static function deleteItem($bucket, $cache)
    {
        $apc_name = Config::CACHE_ID() . ':' . $bucket . '/' . $cache;
        if (apc_exists($apc_name))
        {
            apc_delete($apc_name);
        }
    }

    public static function deleteBucket($bucket)
    {
        if (isset(self::$buckets[$bucket]))
        {
            unset(self::$buckets[$bucket]);
        }

        $path = CONFIG::CACHE_PATH() . $bucket . '.cache';
        if (file_exists($path))
        {
            unlink($path);
        }
    }

    public static function save($cache, $value, $bucket = NULL)
    {
        self::initBucket($bucket);

        self::$buckets[$bucket][$cache] = $value;
    }

    private static function initBucket(&$bucket)
    {
        if (!$bucket)
        {
            $bucket = self::$requestBucket;
        }

        $bucket = str_replace('/', '_', $bucket);

        if (!isset(self::$buckets[$bucket]))
        {
            self::$buckets[$bucket] = array( );
        }
    }

    private static function loadBucket($bucket)
    {
        $path = CONFIG::CACHE_PATH() . $bucket . '.cache';
        if
        (
            (
                !isset(self::$buckets[$bucket])                ||
                !isset(self::$buckets[$bucket]['/complete/'])
            )                                                  &&
            file_exists($path)
        )
        {
            self::initBucket($bucket);

            self::$buckets[$bucket] += unserialize
            (
                file_get_contents($path)
            );
            self::$buckets[$bucket]['/complete/'] = true;

            return true;
        }

        return false;
    }

    public static function finalise()
    {
        foreach (self::$buckets as $bucket => $items)
        {
            if (isset(self::$buckets[$bucket]['/ignore/'])) continue;

            self::loadBucket($bucket);
            unset(self::$buckets[$bucket]['/complete/']);

            $apcname = Config::CACHE_ID() . ':' . $bucket . '/';
            $save    = false;
            $objs    = array( );

            foreach (self::$buckets[$bucket] as $cache => $obj)
            {
                if (is_string($obj))
                {
                    $objs[$cache] = $obj;

                    continue;
                }

                if (!$obj->valid())
                {
                    apc_delete($apcname . $cache);

                    continue;
                }

                $objs[$cache] = serialize($obj);

                if ($obj->dirty())
                {
                    apc_store($apcname . $cache, $obj);

                    $save = true;
                }
            }

            if ($save)
            {
                file_put_contents
                (
                      $this->config->path
                    . str_replace('/', '_', $bucket) . '.cache',
                      serialize($objs)
                );
            }
        }
    }

    public static function ignoreBucket($bucket = NULL)
    {
        self::initBucket($bucket);

        self::$buckets[$bucket]['/ignore/'] = true;
    }
}

// }}}

?>