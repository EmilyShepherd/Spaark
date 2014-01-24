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
                $obj->json->parseFile(SPAARK_PATH . 'default/config')
            )
        );

        return $obj;
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
        $arr = $this->json->parseFile($this->config->configPath . $name, false);

        if (is_array($this->config->$name))
        {
            $arr = \tree_merge_recursive($arr, $this->config->$name);
        }
        
        $this->loadArray($arr);
    }
}

