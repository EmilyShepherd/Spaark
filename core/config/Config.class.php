<?php namespace Spaark\Core\Config;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * The site config collection
 */
class Config extends ConfigReader
{
    // {{{ static
    
    /**
     * The Config object
     */
    private static $config;
    
    /**
     * Creates a Config object from the given site name and stores it in
     * self::$config
     *
     * @param string $site The name of the file to load, without .scnf
     */
    public static function init($site)
    {
        if (self::$config) return;
        
        self::$config = new Config($site);
    }
    
    /**
     * Gets the specified value
     *
     * @param string $value The setting to get
     * @return string The value of the specified setting
     */
    public static function getVal($value)
    {
        if (!self::$config) self::init('app');
        
        return self::$config->getValue($value);
    }
    
    /**
     * Returns the value of the asked for setting (method name)
     *
     * Used to make referencing Config settings feel like using
     * constants:
     *   Config::MY_VALUE() returns the value of the MY_VALUE setting
     * This is the same as doing Config::getVal('MY_VALUE')
     * 
     * @param string $method The setting to get
     * @return string The value of the specified setting 
     * @see Config::getVal()
     */
    public static function __callStatic($method, $args)
    {
        return self::getVal($method);
    }
    
    // }}}
    
        ////////////////////////////////////////////////////////
    
    // {{{ instance
    
    /**
     * Reads the Config file at the specified location.
     *
     * @param string $site The name of the file to load, without .scnf
     */
    public function __construct($site)
    {
        parent::__construct
        (
            file_get_contents($site . '.scnf'),
            FILE_FULL
        );
    }
    
    // }}}
}

?>