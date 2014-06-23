<?php namespace Spaark\Core\Model;
/**
 *
 */

// {{{ Exceptions

    /**
     * Thrown when init() fails
     */
    class CannotInitMasterException extends \Exception
    {
        /**
         * Constructor
         *
         * @param string $class The class name
         */
        public function __construct($class, $msg)
        {
            parent::__construct
            (
                'Cannot initialise master for ' . $class . ': ' . $msg
            );
        }
    }

    /**
     * Thrown when init() is called for a class does not have a
     * initMaster() method
     *
     * This either means you are calling init() unnessicarily, or you
     * have forgotten to implement initMaster()
     */
    class MissingInitMasterException extends CannotInitMasterException
    {
        /**
         * Constructor
         *
         * @param string $class The class name
         */
        public function __construct($class)
        {
            parent::__construct($class, 'initMaster() not defined');
        }
    }

    /**
     * Thrown when initMaster() does not return an object
     */
    class InvalidInitMasterException extends CannotInitMasterException
    {
        /**
         * Constructor
         *
         * @param string $class The class name
         */
        public function __construct($class)
        {
            parent::__construct
            (
                $class,
                'initMaster() did not return an object'
            );
        }
    }

    /**
     * Thrown when a master object that needs to be initialised
     * beforehand but hasn't is referenced
     *
     * Eg:
     * <code>
     *   class Config
     *   {
     *       protected static function initMaster()
     *       { /* Important Stuff * / }
     *
     *       public function doSomething()
     *       {
     *           // Attempts to load the master config, but can't
     *           return $this->config->value;
     *       }
     *   }
     *   
     *   //Config::init(); NOT called
     *   $this->config->doSomething();
     * </code>
     */
    class MasterNotInitialisedException extends \Exception
    {
        /**
         * Constructor
         *
         * @param string $class The class name
         */
        public function __construct($class)
        {
            parent::__construct
            (
                  'Could not load the master for ' . $class . '::'
                . 'init() has not been called'
            );
        }
    }

    // }}}

        ////////////////////////////////////////////////////////

/**
 * Master classes act in similar ways to normal Entities, with the
 * exception that they also make use of a member variable of the same
 * class (ie the overarching parent or master) which needs to be
 * constructed in some special manner.
 *
 * This class supports master construction either beforehand, or lazily.
 *   + For lazy loading, use buildMaster(), which acts just like
 *     _fromModel()
 *   + For beforehand loading, specify a initMaster() which can take
 *     any arguments you need. Call it with init() (this will
 *     automatically call your initMaster, and cache its response)
 *
 */
abstract class Master extends Entity
{
    /**
     * If you need to initialise the master with special arguments,
     * call this (otherwise it'll automatically be built when required)
     */
    public static function init()
    {
        $func = get_called_class() . '::initMaster';

        // Someone is trying to init() a class that doesn't need
        // initialising. In theory I could just quietly ignore this, but
        // I'm gonna be difficult about it.
        if (!is_callable($func))
        {
            throw new MissingInitMasterException(get_called_class());
        }

        $obj  = call_user_func_array($func, func_get_args());

        if (!$obj)
        {
            throw new InvalidInitMasterException(get_called_class());
        }
        
        static::cache($obj, 'model', get_class($obj));
        
        return $obj;
    }

    /**
     * Creates an instance of the object. If this is building the
     * master, it will call buildMaster(), otherwise __fromModel()
     *
     * @param Entity $model The model
     * @return Master The instanciated object
     * @throws CannotCreateModelException
     */
    public static function _fromModel($class)
    {
        // Standard case - just pass it off to be fromModel'd like
        // normal
        if ($class != get_called_class())
        {
            return static::build($class);
        }
        elseif (!is_callable($class . '::initMaster'))
        {
            return static::buildMaster($class);
        }
        else
        {
            throw new MasterNotInitialisedException($class);
        }
    }

    /**
     * As this class uses _fromModel(), the build operation is passed
     * here to be overriden if required
     *
     * @param Model $model The model to build for
     * @return Master      The loaded master object
     */
    protected static function build($class)
    {
        return static::fromModel($class, NULL, STATIC_F + 1);
    }

    /**
     * This method is called to build the master, override it to
     * implement your own behaviour
     *
     * The default behaviour is to construct as a normal model
     *
     * @param Model $model The model to build for
     * @return Master      The loaded master object
     */
    protected static function buildMaster($class)
    {
        return static::build($class);
    }

    public static function getMaster()
    {
        return static::fromModel(get_called_class());
    }
}