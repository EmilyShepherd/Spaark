<?php namespace Spaark\Core\Model;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


use \Spaark\Core\Error\NoSuchMethodException;


/**
 *
 */
class Collection extends Base\Model implements \Iterator, \Countable
{
    /**
     * Array of boxed objects in this collection
     */
    private $objs    = array( );
    
    /**
     * Raw data source data (unboxed)
     *
     * This is boxed lazily as required
     */
    private $res;
    
    private $class;
    
    /**
     * The current position within an iterator loop
     */
    private $current = 0;
    
    /**
     * If true, boxed objects won't be cached into memory
     * 
     * This is useful if the data collection is too big to all reside in
     * local memory and/or each value will only be used inside the loop
     */
    private $noCache = false;
    
    public function __construct($res, $class)
    {
        $this->res   = $res;
        $this->class = $class;
    }
    
    /**
     * Sets the Collection to immediately dump results after they are
     * iterated over
     *
     * @see $noCache
     */
    public function massive()
    {
        $this->noCache = true;
        
        return $this;
    }
    
    /**
     * Returns this Model's name
     *
     * Similar to Model::getName(), except the default name has
     * "Collection" stripped off the end, if present. Ie:
     *   BookCollection's default name would be Book
     *
     * @param boolean $withNS If true, the namespace is included
     */
    public function getName($withNS = false)
    {
        $class = get_class($this);
        
        if (defined($class . '::NAME'))
        {
            $class =  constant($class . '::NAME');
            
            if ($withNS)
            {
                $parts = explode('\\', $class);
                array_pop($parts);
                $class = implode('\\', $parts) . '\\' . $class;
            }
        }
        else
        {
            $parts = explode('\\', $class);
            $class = array_pop($parts);
            
            if (substr($class, -10) == 'Collection')
            {
                $class = substr($class, 0, -10);
            }
            
            if ($withNS)
            {
                $class = implode('\\', $parts) . '\\' . $class;
            }
        }
        
        return $class;
    }
    
    /**
     * Rewinds back to the first element of the Iterator
     *
     * @see http://www.php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->current = 0;
        
        if ($this->res) $this->res->rewind();
    }
    
    /**
     * Returns the current element
     *
     * @see http://www.php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        if (isset($this->objs[$this->current]))
        {
            return $this->objs[$this->current];
        }
        else
        {
            $class = $this->class;
            $row   = $this->res->current();
            $obj   = Entity::getObj('id', $row['id'])
                ?: new $class();
            
            $obj->__fromArray($row);
            
            if (!$this->noCache)
            {
                $class::cache($obj, 'id', $row['id']);
            }
            
            if ($this->current + 1 == count($this->res))
            {
                $this->res->free();
                $this->res = NULL;
            }   
            
            return $this->objs[$this->current] = $obj;
        }
    }
    
    /**
     * Moves the current position to the next element
     *
     * @see http://www.php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->current++;
        
        if ($this->res) $this->res->next();
    }
    
    /**
     * This method is called after rewind() and next() to check if the
     * current position is valid
     *
     * @see http://www.php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        return $this->current < $this->count();
    }
    
    /**
     * Returns the key of the current element
     *
     * @see http://www.php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return $this->res->key();
    }
    
    /**
     * This method is executed when using the count() function on this
     * object
     *
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        return $this->res ? $this->res->count()
                          : count($this->objs);
    }
}

