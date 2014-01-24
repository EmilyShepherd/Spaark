<?php namespace \Spaark\Core\Model\Sources\Cache;

class APC extends Cache
{
    protected function _get($pos)
    {
        return array($this->model[0] => $this->res[$pos]);
    }

    protected function _read()
    {
        $this->res = apc_fetch($this->where)
    }
}