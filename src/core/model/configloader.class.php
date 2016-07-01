<?php namespace Spaark\Core\Model;

class ConfigLoader extends Base\Wrapper
{
    public function __fromModel($model)
    {
        $this->loadConf($model);
    }

    public function __fromController($controller)
    {
        $this->loadConf($controller);
    }

    protected function loadConf($class)
    {
        while ($reflect = $class::getHelper('reflect'))
        {
            $obj = $class . '\\Config';
            if (class_exists($obj))
            {
                $this->object = $obj::fromSingle();

                return;
            }
            else
            {
                $class = $reflect->getParentClass()->getName();
            }
        }

        $this->object = \Spaark\Core\Instance::getConfig();
    }
}

