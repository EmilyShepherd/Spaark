<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents a fragment of HTML code, that can be included at will
 */
class Fragment extends HTMLSegment
{
    /**
     * The file extension for Fragments is .htmbf
     */
    protected $extension = '.htmf';

    /**
     * Parses the document
     *
     * @param string $name The name of the fragment
     * @param array $replace Used as $varsIn
     */
    public function __construct($name, $replace = array( ))
    {
        $this->setName($name);

        if (isset($replace['CLASS']))
        {
            require_once Config::CONTROLLER_PATH() . $replace['CLASS'] . '.class.php';

            $class = pathinfo($replace['CLASS'], PATHINFO_BASENAME);
            $obj   = new $class();

            ob_clean();

            $obj->$name($replace);

            $rawHTML = ob_get_contents();

            ob_clean();

            $this->static = true;
        }
        else
        {
            $rawHTML = $this->loadFromFile();
            $this->varsIn = $replace;
        }

        $this->buildHTML($rawHTML);

        $this->response = $replace;
        $this->html     = $this->replaceVars($this->html);
    }

    protected function replaceVarsCB($matches)
    {
        $name = $matches[3];

        if ($matches[1] == '\\')
        {
            return $matches[0];
        }
        else if ($matches[2] == '@')
        {
            return
                isset($this->response[$name])
                  ? $this->response[$name]
                  : '';
        }
        else
        {
            return $matches[0];
        }
    }

    /**
     * Returns the generated HTML
     *
     * @return The generated HTML
     */
    public function getHTML()
    {
        if ($this->static)
        {
            return $this->html;
        }
        else
        {
            extract($this->varsIn);

            ob_clean();

            eval('?>' . $this->html);

            $html = ob_get_contents();

            ob_clean();
            return $html;
        }
    }
}

