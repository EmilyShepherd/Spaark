<?php

class Collection extends \Spaark\Base\Model
{
    const POLICY = NULL;

    protected $policy;

    public function __construct()
    {
        if (self::POLICY)
        {
            $this->policy = new CollectionPolicy(self::POLICY);

            $this->model = $this->policy->getModel();
        }
    }

    public static function __callStatic($name, $args)
    {
        if (!substr($name, 0, 7) == 'newFrom') return;

        $name  = 'from' . substr($name, 7);
        $class = get_called_class();
        $class = new $class(NULL);

        return call_user_func_array(array($class, $name), $args);
    }
}

?>