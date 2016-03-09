<?php namespace Spaark\Core\Model\HTML;

class MissingTemplateException extends \Exception
{
    public function __construct($template, $nfe)
    {
        parent::__construct
        (
            'Missing template: ' . $template,
            0,
            $nfe
        );
    }
}

?>