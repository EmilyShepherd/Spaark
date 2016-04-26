<?php namespace Spaark\Core\Model;
/**
 *
 */

use Spaark\Core\Util\Encoding\JSON;
use Spaark\Core\Config as GlobalConfig;
use Spaark\Core\Instance;

class Config extends Base\Singleton
{
    public static function getConf($var)
    {
        return Instance::getConfig()->$var;
    }

    public static function _fromSingle()
    {
        $ret = new static();
        $ret->loadConfig(get_called_class());

        return $ret;
    }

    private function loadConfig($name)
    {
        $json = new JSON();
        $name = explode('\\', $name);
        $name = strtolower($name[count($name) - 2]);
        $path = ROOT . '/' .  Instance::getConfig()->configPath . $name;
        $arr  = \tree_merge_recursive
        (
            $json->parseFile($path, false),
            (array)Instance::getConfig()->propertyValue($name)
        );

        $this->loadArray($arr);
    }
}

