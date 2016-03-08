<?php namespace Spaark\Core\Model\Collection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of lazycollection
 *
 * @author Emily Shepherd
 */
class LazyCollection extends Collection
{
    private $built = array( );
    
    private $collection;
    
    public function next()
    {
        $this->pointer++;
       
    }
}
