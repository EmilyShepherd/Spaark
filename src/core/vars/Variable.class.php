<?php

class Variable implements AbstractVariable
{
    protected $value;
    protected $stringValue;

    const INPUT_TYPE = 'text';

    const REGEX = '*';//'i:hello\s*world';

    public function __construct($value)
    {
        $this->value = $this->stringValue = $value;
    }

    public function __toString()
    {
        return (string)$this->stringValue;
    }

    public function value()
    {
        return $this->value;
    }

    public static function validate($val)
    {
        return true;
    }
}

