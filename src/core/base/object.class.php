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

    protected static $helperObjs = array( );

    protected $objects = array( );

    protected $sessionClass = 'Session';

    protected $configClass = 'Config';

    protected $cacheClass = 'Cache';

    protected $userClass = 'Auth\User';

    const SESSION_HELPER = 'Session';
    const CONFIG_HELPER  = 'ConfigLoader';
    const CACHE_HELPER   = 'Cache';
    const USER_HELPER    = 'Auth\User';

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
    public static function getHelper($var)
    {
        $objs = &self::$helperObjs[get_called_class()];

        if (!isset($objs))
        {
            $objs = array();
        }

        if (isset($objs[$var]))
        {
            return $objs[$var];
        }
        else
        {
            return $objs[$var] = static::buildHelper($var);
        }
    }

    private static function buildHelper($var)
    {
        // Tries to load model's in the calling Object's namespace
        // Allows class strings to be specified without namespace
        $context = substr
        (
            get_called_class(),
            0, strrpos(get_called_class(), '\\')
        );

        $const_name = 'static::' . strtoupper($var) . '_HELPER';
        if (!defined($const_name))
        {
            return NULL;
        }
        else
        {
            $class = constant($const_name);

            if ($class{0} != '\\')
            {
                $class = ClassLoader::loadModel($class, $context);
            }
        }

        if (class_exists($class))
        {
            if (is_subclass_of($class, '\Spaark\Core\Model\Base\Singleton'))
            {
                return $class::fromSingle();
            }
            else
            {
                $method = 'from' . static::FROM;
                return $class::$method(get_called_class());
            }
        }
    }

    public function __get($var)
    {
       return static::getHelper($var);
    }

    public function __toString()
    {
        return get_class($this) . '[' . spl_object_hash($this) . ']';
    }
}

