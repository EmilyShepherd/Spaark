<?php namespace Spaark\Core\Model\Reflection;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of type
 *
 * @author Emily Shepherd
 */
class Type extends Reflector
{
    protected $acceptedParams = array
    (
        'type'    => '',
        'isArray' => '',
        'key'     => ''
    );
    
    protected $type;
    
    protected $isArray = false;
    
    protected $key = NULL;
    
    public function __construct($type, $array = false, $key = NULL)
    {
        $this->type    = $type;
        $this->isArray = $array;
        $this->key     = $key;
    }
    
    public function isClass()
    {
        return !in_array
        (
            $this->type,
            array('mixed', 'bool', 'int', 'float', 'null', 'void', 'string')
        );
    }
    
    public function isStandalone()
    {
        return
            $this->isClass() &&
            (
                is_subclass_of($this->type, '\Spaark\Core\Model\Base\Entity') ||
                !is_subclass_of($this->type, '\Spaark\Core\Model\Base\Model')
            );
    }
    
    public function getReflect()
    {
        $class = $this->type;
        return $class::getHelper('reflect');
    }
}
