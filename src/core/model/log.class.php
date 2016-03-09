<?php namespace Spaark\Core\Model;
/**
 *
 */

class Log extends Base\Entity
{
    const ERROR  = 'error';
    const WARN   = 'warn';
    const INFO   = 'info';
    const DEBUG  = 'debug';

    private $messages = array( );

    public function error($msg)
    {
        $this->log($msg, self::ERROR);
    }

    public function warn($msg)
    {
        $this->log($msg, self::WARN);
    }

    public function info($msg)
    {
        $this->log($msg, self::INFO);
    }

    public function debug($msg)
    {
        $this->log($msg, self::DEBUG);
    }

    public function log($msg, $level)
    {
        $this->messages[] = array($message, $level);
    }
}

?>