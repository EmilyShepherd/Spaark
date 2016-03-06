<?php namespace Spaark\Core\Model\Base;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Spaark\Core\Util\DataMapper\RelationalConverter;
use Spaark\Core\Util\DataMapper\DimensionalConverter;
use Spaark\Core\Error\NoSuchMethodException;

/**
 * Description of composite
 *
 * @author Emily Shepherd
 */
abstract class Composite extends Model
{
    /**
     * Array of the attribute names that came from a data source
     */
    protected $attrs      = array( );

    /**
     * Saves the state of the objects properties since the last save
     * 
     * This is used to check which properties are dirty and in need
     * of saving when save() is called
     *
     * @see static::save()
     */
    protected $properties = array( );
    
    /**
     * If true, changes have been made that require this entity to be
     * resaved
     */
    protected $dirty    = false;

    /**
     * Constructs a blank copy of the entity by saving the state of each
     * of its properties
     *
     * NB: The constructor is NOT called when the object is loaded via
     * findBy or from, use the appropriate magic functions for that
     */
    public function __construct()
    {
        foreach ($this->reflect->getProperties() as $prop)
        {
            $this->properties[$prop->getName()] =
                $prop->getValue($this);
        }
    }

    /**
     * Returns the loaded source
     *
     * @return string The name of the loaded source
     */
    public function getLoadedSource()
    {
        return $this->loadedSource;
    }

    /**
     * Sets the loaded source
     *
     * @param string $source The name of the loaded source
     */
    public function setLoadedSource($source)
    {
        $this->loadedSource = $source;
    }
    
    /**
     * Sets the Entity's attributes based on the given array
     *
     * Please use loadArray instead
     *
     * @see loadArray()
     * @param array $array The attributes to use
     * @deprecated
     */
    public function __fromArray($array)
    {
        $this->loadArray($array);
    }

    /**
     * Sets the Entity's attributes based on the given array
     *
     * @param array $array The attributes to use
     */
    public function loadArray($array)
    {
        $this->attrs = array( );
        $this->dirty = false;
        $this->new   = false;

        foreach ($array as $key => $value)
        {
            $this->properties[$key] = $value;
            
            if (!$this->reflect->hasProperty($key))
            {
                $this->attrs[$key] = $value;
            }
            else
            {
                $prop = $this->reflect->getProperty($key);
                $prop->setValue($this, $value);
            }
        }
    }
    
    /**
     * Gets a value
     *
     * @param string $var The variable name
     * @return mixed The value of that variables. NULL if it doesn't
     *     exist
     */
    public function getValue($var)
    {
        return \iget($this->attrs, $var);
    }
    
    /**
     * Gets an attribute
     *
     * @param string $var The attribute name
     * @return mixed The attribute value if it exists, otherwise it
     *     attempts to load it as a class
     */
    public function __get($var)
    {
        if ($var !== 'reflect' && $value = $this->propertyValue($var))
        {
            return $value;
        }
        else
        {
            return parent::__get($var);
        }
    }

    /**
     * Returns the value of the given property
     *
     * @param string $var The name of the property to read
     * @param boolean $onlyReadable If true, only readable properties
     *     can be accessed
     * @return mixed The value of the property
     * @throws PropertyNotReadableException If trying to read a not-
     *     readable property with $onlyReadable set to true
     */
    public function propertyValue($var, $onlyReadable = true, $init = true)
    {
        if (isset($this->attrs[$var]))
        {
            return $this->attrs[$var];
        }
        elseif ($this->reflect->hasProperty($var))
        {
            $prop = $this->reflect->getProperty($var);

            if (!$onlyReadable || $var === 'id' || $prop->readable)
            {
                return $this->initialiseProperty($prop, $init);
            }
            else
            {
                throw new PropertyNotReadableException($var);
            }
        }
    }
    
    private function initialiseProperty($prop, $init = true)
    {
        $value = $prop->getValue($this);
        
        if ($init && $value === NULL && $prop->writable)
        {
            if ($prop->type->isArray)
            {
                $value = array( );
            }
            elseif ($prop->type->isClass)
            {
                $class = static::load($prop->type->type);

                if ($class)
                {
                    $value = new $class();
                }
            }

            $prop->setValue($this, $value);
        }

        return $value;
    }

    private function setProperty($var, $val, $onlyWritable = true)
    {
        if ($this->reflect->hasProperty($var))
        {
            $prop = $this->reflect->getProperty($var);

            if (!$onlyWritable || $prop->writable)
            {
                switch ($prop->type->type)
                {
                    case 'int':
                        $val = (int)$val;
                        break;

                    case 'float':
                        $val = (float)$val;
                        break;

                    case 'string':
                        $val = (string)$val;
                        break;

                    case 'boolean':
                        $val = (boolean)$val;
                        break;

                    case 'array':
                        $val = (array)$val;

                    case NULL:
                        break;

                    default:
                        if (!is_a($val, $prop->type->type))
                        {
                            //throw new \Exception('err');
                        }
                }
                $prop->setValue($this, $val);
            }
            else
            {
                throw new PropertyNotWritableException($var);
            }
        }
        else
        {
            $this->attrs[$var] = $val;
        
            if (!isset($this->properties[$var]))
            {
                $this->properties[$var] = ($val === NULL ? TRUE : NULL);
            }
        }
    }
    
    /**
     * Sets the value of an attribute
     *
     * @param string $var The attribute name
     * @param mixed  $val The value to set
     */
    public function __set($var, $val)
    {
        $this->setProperty($var, $val);
    }

    /**
     * Returns the object as an array
     *
     * @param boolean $onlyDirty If true, only dirty properties will be
     *     returned in the array
     * @return array The array of the object's properties
     */
    public function __toArray($onlyDirty = false, $relational = false)
    {        
        $converter =
            $relational
            ? new RelationalConverter($onlyDirty, $this)
            : new DimensionalConverter($onlyDirty, $this);

        return $converter->toArray();
    }
    
    public function __call($method, $args)
    { 
        if (substr(strtolower($method), 0, 5) === 'addto')
        {
            return $this->addTo(substr($method, 5), $args);
        }
        else
        {
            throw new NoSuchMethodException(get_class(), $method);
        }
    }
    
    public function addTo($name, $args)
    {
        $val = is_array($args[0]) ? $args[0] : array($args[0]);
        $var = lcfirst($name);
        
        if ($this->reflect->hasProperty($var))
        {
            $prop = $this->reflect->getProperty($var);
            
            if ($prop->type->isArray)
            {
                $this->_addTo($prop, $var, $val);
            }
        }
    }
    
    private function _addTo($prop, $var, $val)
    {
        $this->addValueToProperty($prop, $val);
        
        if ($prop->type->key === $var)
        {
            foreach ($val as $obj)
            {
                $obj->addValueToProperty($prop, array($this));
            }
        }
    }
    
    private function addValueToProperty($prop, $val)
    {
        $prop->setValue($this,
            array_merge
            (
                (array)$prop->getValue($this),
                $val
            )
        );
    }
}
