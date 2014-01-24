<?php namespace Spaark\Core;

use \Spaark\Core\Config\Config;

// {{{ Exceptions

    /*
    class MissingClassException extends \Exception {}
    class InvalidClassException extends MissingClassException {}
    */

    /**
     * Thrown when a class isn't defined inside its filename
     *
     * Eg:
     * <code>
     *   // myclass.class.php
     *   class NotMyClass
     *   {
     *       //
     *   }
     * </code>
     */
    class NoClassInFileException extends \Exception
    {
        public function __construct($class)
        {
            parent::__construct
            (
                $class . ' not declared in its file'
            );
            
            $this->line = 'ClassLoader';
            $this->file = '{Spaark}';
        }
    }

    // }}}

        ////////////////////////////////////////////////////////

class ClassLoader extends \Spaark\Core\Base\StaticClass
{
    /**
     * Mapping of namespaces starts to file paths
     */
    private static $starts = array( );
    
    /**
     * Used when loading classes to prevent infinate loops
     */
    private static $triedModel = false;

    /**
     * Initialises the ClassLoader
     */
    public static function init()
    {
        $ns = strtolower(trim(Config::NAME_SPACE(), '\\'));

        self::$starts['spaark'] = SPAARK_PATH;
        self::$starts[$ns]      = ROOT;
        
        spl_autoload_register('Spaark\Core\ClassLoader::autoload');
    }
    
    /**
     * This is the autoload function. DO NOT CALL THIS. Call load()
     * instead!
     *
     * @param string $class    The class to load
     * @param bool   $tryModel If true, it will try different namespaces
     * @return bool            Whether the class was loaded or not
     * @see load()
     * @see _load()
     */
    public static function autoload($class, $tryModel = true)
    {
        self::$triedModel = false;
        
        return self::_load($class, $tryModel);
    }
    
    /**
     * Internal function to load a class
     *
     * If $tryModel is try, it will also try to load the class from
     * other namespaces (useful if you cba to use "use" statements
     * properly). If a model is loaded correctly, the class is
     * aliased to ensure autoloads work. (Warning, this can lead to
     * name collisions. If you have classes in different namespaces
     * with the same name, always specify them properly!)
     *
     * @param string $class    The class to load
     * @param bool   $tryModel If true, it will try different namespaces
     * @return bool            Whether the class was loaded or not
     */
    private static function _load($class, $tryModel = true)
    {
        $class = ltrim($class, '\\');
        $parts = explode('\\', $class);
        $first = strtolower($parts[0]);
        
        if (isset(self::$starts[$first]))
        {
            $_parts = $parts;
            
            unset($_parts[0]);
            
            $path =
                  self::$starts[$first]
                . strtolower(implode(DS, $_parts)) . '.class.php';
        }
        else
        {
            $path =
                ROOT. strtolower(implode(DS, $parts)) . '.class.php';
        }
        
        if (self::getFile($path, $class))
        {
            return true;
        }
        elseif ($tryModel && !self::$triedModel)
        {
            $model = $parts[count($parts) - 1];
            
            if ($model = self::loadModel($model))
            {
                class_alias($model, $class);
                
                return true;
            }
        }

        return false;
    }
    
    /**
     * Loads a class safely (if it is already loaded, it won't load it
     * again)
     *
     * @param string $name     The class to load
     * @param bool   $tryModel If true, it will try different namespaces
     * @return bool            Whether the class was loaded or not
     */
    public static function load($name, $tryModel = true)
    {
        return
            class_exists($name, false) || interface_exists($name, false)
            ?: self::autoLoad($name, $tryModel);
    }
    
    /**
     * Attempts to load a model from different namespaces.
     *
     * It tries (in order):
     *   + The local namespace (if provided)
     *   + The app's model namespace
     *   + Spaark's model namespace
     *
     * @param string $name The model to load
     * @param string $localScope The local namespace to try
     * @param mixed If successful, the full name of the loaded class.
     *     False otherwise
     */
    public static function loadModel($name, $localScope = NULL)
    {
        self::$triedModel = true;
        
        //Local Scope
        if ($localScope)
        {
            $fullName = $localScope. '\\' . $name;
                
            if (self::_load($fullName))
            {
                return $fullName;
            }
        }
        
        //App Model Scope
        $fullName = substr(Config::NAME_SPACE(), 1) . 'Model\\' . $name;
        if (self::_load($fullName))
        {
            return $fullName;
        }
        
        //Spaark Model Scope
        $fullName = 'Spaark\\Core\\Model\\' . $name;
        if (self::_load($fullName))
        {
            return $fullName;
        }

        return false;
    }
    
    /**
     * Attempts to load a class from the given file
     *
     * @param string $file  The filename to load
     * @param string $class The class / interface to check for
     * @return bool         If the file was successfully loaded
     * @throws NoClassInFileException If the file existed, but the class
     *     was not specified inside it
     */
    private static function getFile($file, $class)
    {
        if (!file_exists($file))
        {
            return false;
        }
        else
        {
            require_once($file);

            $name =
                substr($class, strrpos($class, '\\') + 1) . '_onload';
            
            if
            (
                !class_exists($class, false) &&
                !interface_exists($class, false)
            )
            {
                throw new NoClassInFileException($class);
            }
            elseif (method_exists($class, $name))
            {
                $class::$name();
            }
            
            return true;
        }
    }
    
    /*
     * @deprecated
     *
    private static function makeClass($parts, $construct)
    {
        $alias    = array_pop($parts);
        $ns       = implode('\\', $parts);
        $class    = end($parts);
        
        if (class_exists($ns))
        {
            $table = defined($ns . '::NAME') ? ''
                : 'const NAME=\'' . strtolower($class) . '\';';
            
            eval
            (
                  'namespace ' . $ns . ';'
                . 'class ' . $alias . ' extends \\' . $ns
                . '{'
                .     $table
                .     (!$construct ? '' : 
                         'public function __construct()'
                .        '{'
                .            'self::build'
                .             '('
                .                 '$this,'
                .                '\'' . $alias . '\','
                .                'func_get_args()'
                .            ');'
                .        '}'
                      )
                . '}'
            );
        }
    }
    */
}