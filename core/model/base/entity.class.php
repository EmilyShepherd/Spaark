<?php namespace Spaark\Core\Model\Base;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Model\Database\SQLException;
use \Spaark\Core\Error\NoSuchMethodException;

// {{{ Constants

    /**
     * Enum for L1 cache
     */
    const L1_CACHE  = 1;

    /**
     * Enum for L2 cache
     */
    const L2_CACHE  = 2;

    /**
     * Enum for L3 cache
     */
    const L3_CACHE  = 3;

    /**
     * Enum for static from method
     */
    const STATIC_F  = 4;

    /**
     * Enum for instanct from method
     */
    const DYN_F     = 5;

    /**
     * Enum for data source
     */
    const SOURCE    = 6;

    // }}}

        ////////////////////////////////////////////////////////

// {{{ Exceptions

    /**
     * Thrown when a non-existant static method is called, that begins
     * with "from"
     */ 
    class NoSuchFindByException extends NoSuchMethodException
    {
        private $obj;
        
        public function __construct($method, $obj)
        {
            parent::__construct($obj, $method);
            
            $this->obj = $obj;
        }
        
        public function getObj()
        {
            return $this->obj;
        }
    }

    /**
     * @deprecated
     */
    class InvalidFindByException extends NoSuchFindByException {}

    /**
     * Thrown when trying to write to a property which is not writable
     */
    class PropertyNotWritableException extends \Exception
    {
        /**
         * Constructs the exception
         *
         * @param string $property The name of the property
         */
        public function __construct($property)
        {
            parent::__construct($property . ' is not writable');
        }
    }

    /**
     * Thrown when trying to read a property which is not readable
     */
    class PropertyNotReadableException extends \Exception
    {
        /**
         * Constructs the exception
         *
         * @param string $property The name of the property
         */
        public function __construct($property)
        {
            parent::__construct($property . ' is not readable');
        }
    }

    // }}}

        ////////////////////////////////////////////////////////

/**
 * Represents a complex model, that contains a series of attributes
 * obtained from a data source, and as such should be cached in local
 * memory
 *
 * Eg:
 * <code>
 *   //Query data source for id 4, create object, cache and return
 *   Entity::fromId(4);
 *
 *   //Notice that a cached object with id=4 already exists, so return
 *   //that instead of querying data source
 *   Entity::fromId(4);
 * </code>
 *
 * It will also cache accross keys:
 * <code>
 *   //Query data source for id 4, create object, cache and return
 *   Entity::fromEmail('email@example.com');
 *   //returns Entity{id: 9, email: 'email@example.com', name: 'Joe'}
 *
 *   //Even though the above Entity was created from the email key, the
 *   //cache will check its id and return that anyway
 *   Entity::fromId(9);
 * </code>
 */
class Entity extends Model
{
// {{{ static
    
    /**
     * The cache of constructed objects
     */
    public static $_cache = array( );

    protected static $source;
    
    // TODO: Cache is broken
    /**
     * Returns the given class from cache given it's $id = $val
     *
     * @param string $key   The key to check against
     * @param scalar $val   The value to match
     * @return Entity The cached object, or NULL
     */
    public static function getObj($key, $val)
    {
        $class = get_called_class();
        
        //If this class name has no entry, make one
        if (!isset(self::$_cache[$class]))
        {
            self::$_cache[$class] = array
            (
                'id' => array( )
            );

            return NULL;
        }
        
        //If this class doesn't have this key type, make one and index
        //everything against it
        if (!isset(self::$_cache[$class][$key]) && false)
        {
            self::$_cache[$class][$key] = array( );
            
            foreach (self::$_cache[$class]['id'] as $obj)
            {
                if (isset($obj->attrs[$key]))
                {
                    self::$_cache[$class][$key][(string)$obj->$key] = $obj;
                }
            }
        }
        
        //If this class has an entry in its key cache for the given
        //value, return it
        if (isset(self::$_cache[$class][$key][$val]))
        {
            return self::$_cache[$class][$key][$val];
        }
    }
    
    /**
     * Caches the given object
     *
     * @param Entity $obj   The object to cache
     * @param string $id    The key to cache it under
     * @param scalar $val   The value to cache it under
     */
    public static function cache(Entity $obj, $id = null, $val = null)
    {
        $class = get_called_class();
        
        if ($id)
        {
            self::$_cache[$class][$id][$val] = $obj;
        }
        
        foreach (array_keys(self::$_cache[$class]) as $key)
        {
            if (isset($obj->attrs[$key]))
            {
                $value = is_array($obj->$key)
                    ? serialize($obj->$key)
                    : (string)$obj->$key;

                self::$_cache[$class][$key][$value] = $obj;
            }
        }
    }
    
    /**
     * Attempts to return an object
     *
     * Options:
     *   + If it's in cache, return that
     *   + Use parent::build() to attempt auto-factory build
     *   + Attempt to build it by querying a data sorce via
     *     __autoBuild()
     *
     * @param string $id    The key to build by
     * @param array  $args  The arguments to use to build
     * @return static The object, if built correct
     * @throws cannotBuildModelException
     *     If all build / load attempts fail
     */
    public static function from($id, $args)
    {
        $class   = get_called_class();
        $id      = lcfirst($id);
        $obj     = NULL;
        $args    = (array)$args;
        $include = \iget($args, 1) ?: array( );
        $startAt = \iget($args, 2, L1_CACHE);
        $val     =
              (!isset($args[0])     ? NULL
            : (is_array($args[0])   ? implode($args[0])
            : (!is_scalar($args[0]) ? (string)$args[0]
            :                         $args[0])));
        
        switch ($startAt)
        {
            // Try Local Cache
            case L1_CACHE:
                if ($obj = static::getObj($id, $val))
                {
                    return $obj;
                }

            case L2_CACHE:
                //
            case L3_CACHE:
                //

            // Try static function
            // _from$id()
            case STATIC_F:
                try
                {
                    $func = $class . '::_from' . $id;

                    if ($obj = call_user_func_array($func, $args))
                    {
                        break;
                    }
                }
                catch (NoSuchMethodException $nsme) { }

            // Try instance method
            // $obj->__from$id()
            case DYN_F:
                try
                {
                    if ($obj = parent::from($id, $args))
                    {
                        break;
                    }
                }
                catch (NoSuchFromException $nsfe) { }

            // Try data source
            case SOURCE:
                try
                {
                    $objs = static::findBy($id, $args);

                    if ($objs->count(true) == 1)
                    {
                        $obj = $objs->get(0);
                        break;
                    }
                }
                catch (NoSuchFindByException $nsfbe) { }

            default:
                throw new CannotCreateModelException
                (
                    $class, $id, $val
                );
        }

        static::cache($obj, $id, $val);
        
        $obj->dirty = false;
        $obj->new   = false;
        
        return $obj;
    }
    
    /**
     * Attempts to return an iterable collection of objects
     *
     * @param string $name The findBy string to use
     * @param array $args  The value to look for, in an array
     * @param boolean $count Not used
     * @return Iterable The list of objects
     * @throws NoSuchFindByException if no findBy function / source is
     *     set
     */
    public static function findBy($name, $args, $count = false)
    {
        try
        {
            $ret = static::call($name, $args, 'findBy');
            return $ret[1];
        }
        catch (NoSuchFindByException $nsfbe)
        {
            if (static::$source)
            {
                $source = static::load(static::$source);
                $source = new $source(get_called_class());

                if (strpos($name, 'Latest') === 0)
                {
                    $source->order(substr($name, 6), 'DESC');
                }
                elseif (strpos($name, 'Highest') === 0)
                {
                    $source->order(substr($name, 7), 'DESC');
                }
                elseif (strpos($name, 'Earliest') === 0)
                {
                    $source->order(substr($name, 8), 'ASC');
                }
                elseif (strpos($name, 'Lowest') === 0)
                {
                    $source->order(substr($name, 6), 'ASC');
                }
                else
                {
                    $source->fwhere($name, iget($args, 0, 1));
                }

                return $source;
            }
        }
        
        throw $nsfbe;
    }
    
    /**
     * Handles magic static functions - used for fromX() and findByX()
     *
     * @param string $name The called function
     * @param array $args  The arguments used in the method call
     * @return mixed The return from the findBy / from method
     * @throws NoSuchMethodException if the method isn't a findBy / from
     * @see self::from()
     * @see self::findBy()
     */
    public static function __callStatic($name, $args)
    {
        if (substr($name, 0, 4) == 'from')
        {
            return static::from(substr($name, 4), $args);
        }
        elseif (substr($name, 0, 6) == 'findBy')
        {
            return static::findBy(substr($name, 6), $args);
        }
        else
        {
            throw new NoSuchMethodException(get_called_class(), $name);
        }
    }

    /**
     * Returns an instance from the given data, either by finding it
     * already cached, or by creating a new one
     *
     * @param array $data The data to create an object from
     * @param boolean $cache If false, newly created instances won't be
     *     cached
     * @return static The loaded / new object
     */
    public static function instanceFromData($data, $cache = true)
    {
        $obj = static::findFromData($data);

        if ($obj)
        {
            $cache = true;
        }
        else
        {
            $obj = static::blankInstance();
        }

        if ($cache)
        {
            static::recacheUsingData($obj, $data);
        }

        $obj->loadArray($data);

        return $obj;
    }

    /**
     * Searches the cache for an object comparing the keys in the cache
     * with the given data
     *
     * @param array $data The data to search for
     * @return static The object, if found. NULL, otherwise
     * @see self::instanceFromData()
     */
    private static function findFromData($data)
    {
        $class = get_called_class();
        
        if (isset(static::$_cache[$class]))
        {
            foreach (static::$_cache[$class] as $key => $values)
            {
                if (isset($data[$key]) && isset($values[$data[$key]]))
                {
                    return $values[$data[$key]];
                }
            }
        }
    }

    /**
     * Recache the given object using the given data
     *
     * This will uncache the object from all the keys it is currently
     * cached under, and recache them, using the data from the given
     * array
     *
     * @param Entity $obj The object to recache
     * @param array $data The data to recache by
     * @see static::instanceFromData()
     */
    private static function recacheUsingData($obj, $data)
    {
        $class = get_class($obj);
        
        if (!isset(static::$_cache[$class]))
        {
            static::$_cache[$class] = array( );
        }

        foreach (static::$_cache[$class] as $key => &$values)
        {
            if (isset($data[$key]))
            {
                $value = $obj->propertyValue($key, false);

                if (isset($values[$value]) && $values[$value] === $obj)
                {
                    unset($values[$value]);
                }

                if (isset($data[$key]))
                {
                    $values[$data[$key]] = $obj;
                }
            }
        }
    }
    
    // }}}
    
        ////////////////////////////////////////////////////////
    
// {{{ object
    
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
     * If true, this is a new object
     */
    protected $new      = true;
    
    /**
     * If true, this will attempt to save on destruction
     */
    protected $autoSave = false;

    /**
     * Records which source this object was loaded from
     */
    protected $loadedSource;

    /**
     * ID
     *
     * @deprecated
     */
    protected $id;

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
     * Saves this to a data source
     */
    public function save()
    {
        // If this was loaded from somewhere, save it back there.
        // Otherwise, save it to the default location
        $source =
              ($this->loadedSource ? $this->loadedSource
            : (static::$source     ? static::load(static::$source)
            :                        NULL));

        if (!$source) return false;

        $source = new $source(get_called_class());
        $data   = $this->__toArray(true);

        if ($this->new)
        {
            $this->id = $source->create($data);
        }
        else
        {
            $source->update($this->id, $data);
        }
        
        $this->new        = false;
        $this->properties = array_merge($this->properties, $data);
    }
    
    /**
     * Deletes this entity from the data source
     */
    public function remove()
    {
        if (!$this->new)
        {
            $this->db->delete($this->id);
            $this->new = true;
        }
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
    private function propertyValue($var, $onlyReadable = true)
    {
        if (isset($this->attrs[$var]))
        {
            return $this->attrs[$var];
        }
        elseif ($this->reflect->hasProperty($var))
        {
            $prop = $this->reflect->getProperty($var);

            if (!$onlyReadable || $prop->readable)
            {
                return $prop->getValue($this);
            }
            else
            {
                throw new PropertyNotReadableException($var);
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
        $this->attrs[$var] = $val;
        
        if (!isset($this->properties[$var]))
        {
            $this->properties[$var] = ($val === NULL ? TRUE : NULL);
        }
    }

    /**
     * Returns the object as an array
     *
     * @param boolean $onlyDirty If true, only dirty properties will be
     *     returned in the array
     * @return array The array of the object's properties
     */
    public function __toArray($onlyDirty = false)
    {
        $array = array( );

        foreach ($this->properties as $key => $original)
        {
            if ($this->reflect->hasProperty($key))
            {
                $prop  = $this->reflect->getProperty($key);
                $value = $prop->getValue($this);

                if (!$onlyDirty || $value != $original)
                {
                    $array[$key] = $value;
                }
            }
        }

        return $array;
    }

    /**
     * If autoSave is enabled, this will save the object at destruct
     * time
     */
    public function __destruct()
    {
        if ($this->autoSave)
        {
            $this->save();
        }
    }
    
    /**
     * Sets autoSave to false to prevent this object from being saved
     * when destroyed
     */
    public function discard()
    {
        $this->autoSave = false;
    }
    
    // }}}
}
    
    