<?php namespace Spaark\Core\Error;

class NotFoundException extends \Exception
{
    public function __construct($file)
    {
        parent::__construct('Could not find ' . $file);
    }
}