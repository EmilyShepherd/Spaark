<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Model\XML\XMLParser;
use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;

class MissingFormException extends \Exception {}

/**
 *
 */
class FormGetter extends HTMLSegment
{
    private $form;

    public function __construct($name)
    {
        $parts = explode('_', $name);
        $fname = explode('.', $parts[0]);

        $this->setName($fname[0]);
        $this->extension = '.' . $fname[1];

        $html = $this->loadFromFile();

        $xml = new XMLParser($html);
        $xml->setNS('\Spaark\Core\Model\HTML\Handlers');
        $xml->setHandler('form', 'FormHandler');
        $xml->name = $parts[0];
        $xml->parse();

        try
        {
            $this->form = Cache::load($name);
        }
        catch (CacheMiss $cm)
        {
            throw new MissingFormException('No Form: ' . $name);
        }
    }

    public function getForm()
    {
        return $this->form;
    }
}