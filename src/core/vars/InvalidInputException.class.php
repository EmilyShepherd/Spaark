<?php

class InvalidInputException extends SystemException
{
    public function __construct()
    {
        parent::__construct('The given input is unacceptable');
    }
}

?>