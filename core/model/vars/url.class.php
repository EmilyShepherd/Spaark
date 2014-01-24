<?php namespace \Spaark\Core\Model\Vars;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


use \Spaark\Core\Config\Config;


/**
 * Represents a URL
 */
class URL extends String
{
    /**
     * Calls the parent constructor
     *
     * @param string $url The url
     */
    public function __construct($url)
    {
        if (!$url)
        {
            $url = Config::HREF_ROOT();
        }
        
        parent::__construct($url);
    }
}

?>