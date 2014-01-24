<?php namespace Spaark\Core\model\sources;

interface iSource extends \Iterator, \Countable, \ArrayAccess
{
    const ASC  = 'ASC';
    const DESC = 'DESC';

    /* public function create($key, $val, $data) */

    /**
     * This should perform a read with the current options, setting
     * the value to $this->res
     */
    /* public function read([$where [, $value]]) */

    /* public function update([$where [, $value]]) */
    
    /* public function delete([$where [, $value]]) */
     
    /* public function where($where [, $value]) */

    /* public function order($by [, $order = "ASC"]) */

    /* public function limit($num [, $start = 0]) */

    /* public function group($group) */

    /* public function include($includes) */

    /* public function massive() */
}