<?php namespace Spaark\Core\Model\Vars;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Config\Config;

/**
 * Represents a password
 */
class NiceURLPart extends String
{
    private $plain;
    
    private $url;
    
    public function __construct($plain)
    {
        $this->plain = $plain;
        $this->url   = str_replace
        (
            array('?', '(', ')', ' / ',  ' - ', ' '),
            array('',  '',  '',  '-or-', '-',   '-'),
            strtolower($plain)
        );
    }
    
    public function __toString()
    {
        return $this->url;
    }
}


?>