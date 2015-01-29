<?php namespace Spaark\Core\Model;
/**
 *
 */

class Config extends Base\Master
{
    protected static function initMaster($name)
    {
        $obj  = static::blankInstance();
        $json = new Encoding\JSON();
        
        $obj->loadArray
        (
            \tree_merge_recursive
            (
                $json->parseFile(ROOT . $name, false),
                $json->parseFile(SPAARK_PATH . 'default/config'),
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
        $this->loadConfig($model);
    }

    public function __fromController($controller)
    {
        $this->loadConfig($controller);
    }
    
    public function __fromParent($parent)
    {
        $this->loadConfig($parent);
    }

    private function loadConfig($name)
    {
        $json    = new Encoding\JSON();
        $reflect = $name::getHelper('reflect');
        $parent  = $reflect ? $reflect->parent : NULL;
        $upConf  = $parent
            ? $parent::getHelper('config')->attrs
            : array( );
        
        $name    = strtolower(substr($name, strrpos($name, '\\') + 1));
        $path    = $this->config->configPath . $name;
        $arr     = \tree_merge_recursive
        (
            $json->parseFile($path, false),
            (array)$this->config->getValue($name),
            $upConf
        );

        $arr['app'] = $this->config->app;
        
        $this->loadArray($arr);
    }
}

