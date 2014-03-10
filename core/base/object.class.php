<?php namespace Spaark\Core\Base;
/**
 *
 */

use \Spaark\Core\ClassLoader;

abstract class Object
{
    const FROM = 'Parent';

    protected static $_config = array
    (
        'helpers' => array
        (
            'session' => 'Session',
            'config'  => 'Config',
            'cache'   => 'Cache',
            'user'    => 'Auth\User'
        ),
        'source' => 'Sources\database\MySQLi'
    );
    
    protected $objects = array( );
    
    protected $sessionClass = 'Session';
    
    protected $configClass = 'Config';
    
    protected $cacheClass = 'Cache';
    
    protected $userClass = 'Auth\User';
    
    /**
     * Handles the mgaic getting of variables
     *
     * Options:
     *   + If a class is defined by $this->{$var . 'Class'}, that will
     *     be instantiate, set to $this->$var, then returned
     *   + Otherwise, NULL
     *
     * @param string $var The magic variable name
     * @return mixed The object
     */
    public function __get($var)
    {
        if (isset($this->objects[$var]))
        {
            return $this->objects[$var];
        }
        else
        {
            // Tries to load model's in the calling Object's namespace
            // Allows class strings to be specified without namespace
            $context = substr
            (
                get_class($this),
                0, strrpos(get_class($this), '\\')
            );

            if (!isset($this->{$var . 'Class'}))
            {
                $class = ClassLoader::loadModel($var, $context);
            }
            else
            {
                $class = $this->{$var . 'Class'};
                
                if ($class{0} != '\\')
                {
                    $class = ClassLoader::loadModel($class, $context);
                }
            }
        }
        
        if (class_exists($class))
        {
            if (is_subclass_of($class, '\Spaark\Core\Model\Singleton'))
            {
                $obj = $class::fromSingle();
            }
            else
            {
                $method = 'from' . static::FROM;
                $obj    = $class::$method(get_class($this));
            }
            
            if ($obj)
            {
                $this->objects[$var] = $obj;
            }
            
            return $obj;
        }
    }
    
    public function __toString()
    {
        return get_class($this) . '[' . spl_object_hash($this) . ']';
    }
}

