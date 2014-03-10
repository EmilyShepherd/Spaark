<?php namespace Spaark\Core\Model\Sources\Database;

use \Spaark\Core\Config\Config;

/**
 * This is a Singleton wrapper for PHP's native MySQLi class.
 *
 * This means that the default behaviour is to keep one connection open,
 * reusing that for each request. If you have more complex stuff to do
 * (such as a different database for searches / admin users) simply
 * update the source object as required. Eg:
 *
 * <code>
 *   class MyMySQLi extends \Spaark\Core\Model\Sources\Database\MySQLi
 *   {
 *       private $master = new MySQLiConnection();
 *       private $slave  = new MySQLiConnection();
 *
 *       protected function execute()
 *       {
 *           if (in_array($this->include, 'search'))
 *               $this->mysqli = $this->slave;
 *           else
 *               $this->mysql  = $this->master;
 *
 *           parent::execute();
 *       }
 *   }
 * </code>
 *
 */
class MySQLiConnection extends \Spaark\Core\Model\Base\Singleton
{
    /**
     * The native MySQLi class
     */
    private $mysqli;

    /**
     * Connects
     */
    public function __construct()
    {
        $this->mysqli = new \MySQLi
        (
            $this->config->host,
            $this->config->username,
            $this->config->password,
            $this->config->database
        );
    }

    /**
     * Routes all method calls to the MySQLi object
     *
     * @param string $func The method
     * @param array $args The arguments
     * @return mixed The return of the method call
     */
    public function __call($func, $args)
    {
        return call_user_func_array(array($this->mysqli, $func), $args);
    }

    /**
     * Routes all member variable gets to the MySQLi object
     *
     * @param string $var The name of the variable to get
     * @return mixed The variable's value
     */
    public function __get($var)
    {
        if ($this->mysqli)
        {
            return $this->mysqli->$var;
        }
        else
        {
            return parent::__get($var);
        }
    }
}