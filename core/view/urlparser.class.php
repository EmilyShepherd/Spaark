<?php namespace Spaark\Core\View;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Instance;

/**
 * Replaces URLs with their cached version
 */
class URLParser
{
    /**
    * Parses a URL to see if it exists in cache.
    *
    * @param string $url The URL to parse
    * @return string The URL to use
    */
    public static function parseURL($url)
    {
        return $url;
        
        if (strpos($url, '://') === false)
        {
            $url   = '/' . ltrim($url, '/');
            
            try
            {
                $cache = Cache::load('output', $url);
                $url  .= '.' . $cache->etag . '.cache';
            }
            catch (CacheMiss $cm)
            {
                Cache::ignoreBucket();
            }
        }
        return $url;
    }
    
    /**
     * Removes whitespace from the given string
     *
     * @param array $symbols Array of symbols that it is safe to remove
     *     spaces around
     * @param string $data The data to remove the whitespace from
     * @return string Whitespace-free data
     */
    public static function compressWhitespace($symbols, $data)
    {
        $search  = array("\r", "\n", "\t", '  ');
        $replace = array(' ',  ' ',  ' ',  ' ');

        foreach ($symbols as $symbol)
        {
            $search[]  = ' ' . $symbol;
            $search[]  = $symbol . ' ';
            $replace[] = $symbol;
            $replace[] = $symbol;
        }

        do
        {
            $last = $data;
            $data = str_replace($search, $replace, $data);
        }
        while ($last != $data);

        return $data;
    }
    
    public static function normalizeURL($url)
    {
        if (substr(Instance::getRequest(), -1) == '/')
        {
            $url = Instance::getRequest() . ltrim($url, '/');
        }
        else
        {
            $url =
                  pathinfo(Instance::getRequest(), PATHINFO_DIRNAME)
                . '/'
                . ltrim($url, '/');
        }
        
        return '/' . ltrim($url, '/');
    }
}

?>