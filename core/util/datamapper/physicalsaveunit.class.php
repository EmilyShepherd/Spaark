<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Emily Shepherd
 */
class PhysicalSaveUnit extends Spaark\Core\Model\Base\Composite
{
    private $links = array( );
    
    public function save()
    {
        
    }
    
    public function brokenLinks()
    {
        return count($this->links);
    }
    
    protected function fixBrokenLinks()
    {
        foreach ($this->links as $attr)
        {
            
        }
    }
}
