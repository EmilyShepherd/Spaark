<?php namespace Spaark\Core\Model\HTML;

use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Vars\Vars;
use \Spaark\Core\View\URLParser;
use \Spaark\Core\Model\HTML\FormGetter;

const VALIDATOR   = 0;
const REQUIRED    = 1;
const FROM        = 2;
const ALLOW       = 0;
const IGNORE      = 1;
const RAISE_ERROR = 2;

class Form extends HTMLSegment implements \Spaark\Core\Cache\Cacheable
{
    private $output;

    private $controlsPOST = false;

    public function __fromName($name)
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
            return Cache::load($name);
        }
        catch (CacheMiss $cm)
        {
            throw new MissingFormException('No Form: ' . $name);
        }
    }

    public static function form()
    {
        if (empty($_POST) || !isset($_POST['_f'])) return false;

        $form = self::fromName($_POST['_f']);

        $_POST = $form->validate($_POST[POST]);

        return true;
    }

    public function __construct($name, $action)
    {
        Cache::save($name, $this, URLParser::normalizeURL($action));
    }

    public function addInput($name, $validate, $required, $from)
    {
        $this->$name = array($validate, (bool)$required, $from);
    }

    public function validate($vars, $unexpected = ALLOW)
    {
        $output = array( );

        foreach ($this->attrs as $name => $settings)
        {
            if (!isset($vars[$name]))
            {
                if ($settings[REQUIRED])
                {
                    throw new MissingRequiredField($name);
                }
            }
            else
            {
                if (isset($settings[FROM]))
                {
                    $class         = $settings[VALIDATOR];
                    $from          = 'from' . $settings[FROM];
                    $output[$name] = $class::$from($vars[$name]);
                }
                else
                {
                    $output[$name] = new $settings[VALIDATOR]($vars[$name]);
                }

                unset($vars[$name]);
            }
        }

        if ($unexpected == ALLOW || empty($vars))
        {
            return $output + $vars;
        }
        elseif ($unexpected == IGNORE)
        {
            return $output;
        }
        else
        {
            throw new \Exception('Unexpected items in form');
        }
    }

    public function valid()
    {
        return true;
    }

    public function dirty()
    {
        return $this->dirty;
    }

    public function save()
    {
        //Cache
    }
}


