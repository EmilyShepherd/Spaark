<?php namespace Spaark\Core\Util\DataMapper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Emily Shepherd
 */
abstract class Mapper
{
    protected $info = array( );
    
    protected $mask = array( );
    
    protected $links = array( );
    
    public function __construct($info, $mask, $links)
    {
        $this->info  = $info;
        $this->mask  = $mask;
        $this->links = $links;
    }
}
