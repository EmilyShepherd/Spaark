<?php namespace Spaark\Core\Util\DataMapper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of dimensionalconverter
 *
 * @author Emily Shepherd
 */
class DimensionalConverter extends BaseMapper
{
    private $attrs;
    
    public function __construct($onlyDirty, \Spaark\Core\Model\Base\Composite $object)
    {
      parent::__construct($onlyDirty, $object);
      
      $this->attrs =
            $this->obj->reflect->getProperty('attrs')->getValue($this->obj);
    }
    
    protected function propertyValue($key)
    {
        if ($this->obj->reflect->hasProperty($key))
        {
            return
                $this->obj->reflect->getProperty($key)->getValue($this->obj);
        }
        elseif (isset($this->attrs[$key]))
        {
            return $this->attrs[$key];
        }
    }
    
    protected function processProperty($key)
    {
        if ($this->obj->reflect->hasProperty($key))
        {
            parent::processProperty($key);
        }
        else
        {
            $this->data[$key] = $this->attrs[$key];
        }
    }
    
    protected function processComposite($converter, $prop)
    {
        $key              = $prop->getName();
        $this->data[$key] = array( );
        $converter->data  = &$this->data[$key];
                
        $converter->_toArray();
    }
    
    protected function processArray($prop)
    {
        $value = (array)$prop->getValue($this->obj);
        $key   = $prop->getName();
        
        if ($prop->type->key && $prop->type->key !== $key)
        {
            $this->external += $value;
        }
        elseif (!$prop->standalone)
        {
            $this->data[$key] = array( );
            
            foreach ($value as $k => $v)
            {
                //TODO: Less shit
                $this->data[$key][$k] = is_object($v) ? (string)$v : $v;
            }
        }
        else
        {
            $this->data[$key . '_ids'] = array( );

            foreach ($value as $k => $v)
            {
                $this->data[$key . '_ids'][$k] = $this->saveExternal($v);
            }
        }
    }
}
