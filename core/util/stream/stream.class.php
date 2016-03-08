<?php namespace Spaark\Core\Util\Stream;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stream
 *
 * @author Emily Shepherd
 */
interface Stream
{
    public function read($bytes);
    
    public function next();

    public function peek();
    
    public function seek($pos);
}