<?php namespace Spaark\Core\Util\DataMapper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Spaark\Core\Model\Base\Composite;

/**
 * Description of relationalconverter
 *
 * @author Emily Shepherd
 */
class RelationalConverter extends BaseMapper
{
    private $prefix = '';

    public $subTables  = array( );
    public $deletedIds = array( );

    protected $id;

    public function __construct($onlyDirty, Composite $object)
    {
        parent::__construct($onlyDirty, $object);

        $this->id = $object->id ?: new IdRef($object);
    }

    protected function canProcess($key, $original)
    {
        return
            $this->obj->reflect->hasProperty($key) &&
            parent::canProcess($key, $original);
    }

    protected function makeNewConverter($value)
    {
        $converter             = parent::makeNewConverter($value);
        $converter->subTables  = &$this->subTables;
        $converter->deletedIds = &$this->deletedIds;

        return $converter;
    }

    protected function set($key, $value)
    {
        $this->data[$this->prefix . $key] = $value;
    }

    protected function propertyValue($key)
    {
        return
            $this->obj->reflect->getProperty($key)->getValue($this->obj);
    }

    protected function processComposite($converter, $prop)
    {
        $converter->prefix   = $prop->getName() . '_';
        $converter->data     = &$this->data;

        $converter->_toArray();
    }

    protected function processArray($prop)
    {
        $key   = $prop->getName();
        $value = (array)$prop->getValue($this->obj);

        if ($prop->type->key && $prop->type->key !== $key)
        {
            $this->external = array_merge($this->external, $value);
        }
        else
        {
            $name                    = $this->obj->modelName;
            $table                   = $name . '_' . $key;
            $loop                    = $value;
            $this->subTables[$table] = array( );

            if ($this->onlyDirty)
            {
                $properties               =
                    $this->obj->getProperty('properties')->getValue($this->obj);
                $original                 = $properties[$key];
                $loop                     = array_diff($original, $value);
                $this->deletedIds[$table] = array( );
            }

            foreach ($loop as $item)
            {
                // Added
                if (!$this->onlyDirty || in_array($item, $value))
                {
                    if ($prop->standalone)
                    {
                        $this->subTables[$table][] = array
                        (
                            $name . '_id' => $this->id,
                            $key  . '_id' => $this->saveExternal($item)
                        );
                    }
                    else
                    {
                        $converter       = $this->makeNewConverter($item);
                        $converter->data = array
                        (
                            $name . '_id' => $this->id
                        );
                        $this->subTables[$table][] = $converter;

                        $converter->_toArray();
                    }
                }
                // Removed
                elseif (!is_object($item))
                {
                    $this->deletedIds[$table][] = $item;
                }
                elseif ($item->id)
                {
                    $this->deletedIds[$table][] = $item->id;
                }
            }
        }
    }
}