<?php namespace Spaark\Core\Model;
/**
 *
 */

class Config extends Master
{
    protected static function initMaster($name)
    {
        $obj = new static();
        
        $obj->loadArray
        (
            \tree_merge_recursive
            (
                $obj->json->parseFile(ROOT . $name, false),
                $obj->json->parseFile(SPAARK_PATH . 'default/config'),
                array('app' => array('root' => ROOT))
            )
        );

        return $obj;
    }

    public static function getConf($var)
    {
        return static::getMaster()->app[$var];
    }
    
    public function __fromModel($model)
    {
        $this->loadConfig($model::modelName());
    }

    public function __fromController($controller)
    {
        $this->loadConfig
        (
            strtolower(substr($controller, strrpos($controller, '\\') + 1))
        );
    }

    private function loadConfig($name)
    {
        $path = $this->config->configPath . $name;
        $arr  = $this->json->parseFile($path, false);

        if (is_array($this->config->$name))
        {
            $arr = \tree_merge_recursive($arr, $this->config->$name);
        }

        $arr['app'] = $this->config->app;
        
        $this->loadArray($arr);
    }
}

