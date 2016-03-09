<?php namespace Spaark\Core\Model\Base;

use \Spaark\Core\Model\Config;

class DefaultModel extends Entity
{
    protected static $source;

    public static function DefaultModel_onload()
    {
        static::$source = Config::getMaster()->defaultSource;
    }
}