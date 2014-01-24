<?php namespace Spaark\Core\Model\HTML;

class MissingStaticVar extends \Exception
{
    public function __construct($var)
    {
        parent::__construct('Missing the static variable: ' . $var);
    }
}

?>