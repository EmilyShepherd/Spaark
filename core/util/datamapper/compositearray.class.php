<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Spaark\Core\Model\Base\Composite;

/**
 * Description of compositearray
 *
 * @author Emily Shepherd
 */
class CompositeArray implements \ArrayAccess, \Iterator
{
    private $obj;
    
    private $props;
    
    private $attrs;
    
    private $onlydirty = false;
    
    private $allowAttrs = true;
    
    private $mask = array( );
    
    private $_cur;
    
    public function __construct(Composite $obj)
    {
        $this->obj   = $obj;
        $this->props = $obj->reflect->getProperty('properties')->getValue($obj);
        $this->attrs = $obj->reflect->getProperty('attrs')->getValue($obj);
        $this->mask  = array_keys($this->props);
    }

    public function current()
    {
        $value = $this->obj->propertyValue($this->_cur, false, false);
        
        return $this->processProp($value);
    }
    
    private function processProp($value)
    {
        if (is_a($value, 'Spaark\Core\Model\Base\Composite'))
        {
            $wrapper = clone $this;
            $wrapper->__construct($value);
            
            return $wrapper;
        }
        elseif (is_array($value))
        {
            foreach ($value as $key => $part)
            {
                $value[$key] = $this->processProp($part);
            }
        }
        else
        {
            return $value;
        }
    }

    public function key()
    {
        return $this->_cur;
    }

    public function next()
    {
        $this->_cur = next($this->mask);
        
        $this->checkCur();
    }
    
    private function checkCur()
    {
        if ($this->_cur && $this->onlydirty)
        {
            $value = $this->obj->propertyValue($this->_cur, false, false);
            
            if ($value === $this->props[$this->_cur])
            {
                $this->next();
            }
        }
    }

    public function offsetExists($offset)
    {

    }

    public function offsetGet($offset)
    {

    }

    public function offsetSet($offset, $value) {

    }

    public function offsetUnset($offset) {

    }

    public function rewind()
    {
        rewind($this->mask);
        
        $this->_cur = current($this->mask);
        
        $this->checkCur();
    }

    public function valid()
    {
        return !!$this->_cur;
    }

}
