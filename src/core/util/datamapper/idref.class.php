<?php namespace Spaark\Core\Util\DataMapper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class NoIdException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Object is missing an ID');
    }
}

/**
 * Description of idref
 *
 * @author Emily Shepherd
 */
class IdRef
{
    private $object;

    public function __construct($object)
    {
        $this->object = $object;
    }

    public function __toString()
    {
        if ($this->object->id)
        {
            return $this->object->id;
        }
        else
        {
            return "0";
        }
    }

    public static function __throwException()
    {
        throw new NoIdException();
    }

    public function __debugInfo()
    {
        return array
        (
            'id' => $this->object->id ?: '{Anonymous Composite}'
        );
    }
}
