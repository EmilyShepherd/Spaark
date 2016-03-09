<?php namespace Spaark\Core\Util\DataMapper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Spaark\Core\Model\Base\Composite;

/**
 *
 *
 * @author Emily Shepherd
 */
abstract class BaseMapper
{
    abstract protected function processArray($property);
    abstract protected function propertyValue($property);
    abstract protected function processComposite($converter, $property);

    protected $onlyDirty;

    protected $data     = array( );

    protected $external = array( );

    protected $waitingFor = array( );

    protected $obj;

    public function __construct($onlyDirty, Composite $object)
    {
        $this->onlyDirty = $onlyDirty;
        $this->obj       = $object;
    }

    public function toArray()
    {
        $this->_toArray();

        return $this->data;
    }

    protected function _toArray()
    {
        $properties =
            $this->obj->reflect->getProperty('properties')->getValue($this->obj);

        foreach ($properties as $key => $original)
        {
            if ($this->canProcess($key, $original))
            {
                $this->processProperty($key);
            }
        }
    }

    protected function canProcess($key, $original)
    {
        if (!$this->onlyDirty)
        {
            return true;
        }

        $val = $this->propertyValue($key);

        return
            ($val !== $original) ||
            (is_a($val, 'Spaark\Core\Model\Base\Composite') && $val->isDirty);
    }

    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    protected function saveExternal($value)
    {
        if (!is_object($value))
        {
            return $value;
        }
        else
        {
            $this->external[] = $value;

            if (!$value->id)
            {
                try
                {
                    $value->save();
                }
                catch (Exception $ex)
                {
                    return new IdRef($value);
                }
            }

            return $value->id;
        }
    }

    protected function processProperty($key)
    {
        $prop = $this->obj->reflect->getProperty($key);

        if ($prop->type->isArray)
        {
            $this->processArray($prop);
        }
        elseif ($prop->type->isClass)
        {
            $this->processObject($prop);
        }
        else
        {
            $this->set($prop->getName(), $prop->getValue($this->obj));
        }
    }

    protected function processObject($prop)
    {
        $value = $prop->getValue($this->obj);
        $key   = $prop->getName();

        if ($prop->type->key)
        {
            $this->external[] = $value;
        }
        elseif ($prop->standalone)
        {
            $this->set($key . '_id', $this->saveExternal($value));
        }
        elseif (is_subclass_of($prop->type->type, 'Spaark\Core\Model\Base\Scalar'))
        {
            //
        }
        else
        {
            $this->processComposite($this->makeNewConverter($value), $prop);
        }
    }

    protected function makeNewConverter($value)
    {
        $converter           = new static($this->onlyDirty, $value);
        $converter->external = &$this->external;

        return $converter;
    }
}
