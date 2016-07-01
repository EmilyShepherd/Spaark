<?php namespace Spaark\Core\Model\XML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Parses an XML document for specific elements, and calls the
 * appropriate handler for each appearance
 */
class XMLParser
{
    /**
     * Namespace for the handlers
     */
    private $ns          = '';

    /**
     * Handlers for element types
     */
    private $handlers    = array( );

    /**
     * Instances of the handlers specified in the $handler array
     */
    private $handlerObjs = array( );

    /**
     * The xml file
     */
    private $xml;

    /**
     * Objects can return other information as well as the updated xml.
     * This array holds any variables set by the handlers, and can be
     * accessed via the calling code
     */
    private $return   = array( );

    /**
     * Sets the xml
     *
     * @param string $xml The XML document to parse
     */
    public function __construct($xml)
    {
        $this->xml  = $xml;
    }

    /**
     * Sets the namespace to be used for the handlers
     */
    public function setNS($ns)
    {
        $this->ns = $ns;
    }

    /**
     * Sets a handler for a given element. Handlers will be called in
     * the order they are set.
     *
     * @param string $tag The element to handle
     * @param string $class The name of the class handler
     */
    public function setHandler($tag, $class, $recurse = false)
    {
        $this->handlers[$tag] = array
        (
            $this->ns . '\\' . $class,
            $recurse
        );
    }

    /**
     * parses the XML document with the set handlers
     *
     * @return string The new XML document
     */
    public function parse()
    {
        $xml = $this->xml;

        foreach (array_keys($this->handlers) as $tag)
        {
            $xml = $this->runParse($tag, $xml);
        }

        return $xml;
    }

    private function runParse($tag, $xml)
    {
        $this->currentTag = $tag;

        return preg_replace_callback
        (
              '/'
            .     '<((' . $tag . ')(:[a-zA-Z]*)?)'
            .        '(.{0}|[\s\t\r\n]+(.*?[^\-\?])?)'
            .     '(>(.*?)<\/\1>|\/>)'
            . '/si',
              array($this, 'loadHandler'),
              $xml
        );
    }

    /**
     * Called when an element is found
     *
     * @param array $matches Array in the form:
     *     1 => tag with namespace
     *     2 => tag / namespace
     *     3 => tagname (if namespace present)
     *     4 => Attributes with leading whitespace
     *     5 => Attribuets without leading whitespace
     *     6 => The rest
     *     7 => If set, the element's content
     * @return string The replacement for this element
     */
    private function loadHandler($matches)
    {
        $tag = strtolower($matches[2]);
        $handler = $this->currentTag;

       //var_dump($this->handlers[$handler]);exit;

        if (!isset($this->handlerObjs[$handler]))
        {
            $this->handlerObjs[$handler] =
                new $this->handlers[$handler][0]($this);
        }

        if ($matches[3])
        {
            $tag = substr($matches[3], 1);
        }

        $content = isset($matches[7]) ? $matches[7] : $matches[6];
        if ($this->handlers[$handler][1])
        {
            $content = $this->runParse($handler, $content);
        }

        $element = $this->handlerObjs[$handler]->parse
        (
            $tag,
            $matches[5],
            $content
        );

        return $element === true ? $matches[0] : $element;
    }

    /**
     * Adds a return
     *
     * @depreciated Use __set() instead
     * @see __set()
     */
    public function addReturn($return, $value)
    {
        $this->__set($return, $value);
    }

    /**
     * Sets a return variable. Values are saved in $this->return
     *
     * @param string $var The variable name
     * @param mixed $val The value
     */
    public function __set($var, $val)
    {
        $this->return[$var] = $val;
    }

    /**
     * Gets a return variable from the $this->return array
     *
     * @param string $var The variable to get
     * @return mixed The value of the variable
     */
    public function __get($var)
    {
        if (isset($this->return[$var]))
        {
            return $this->return[$var];
        }
        else
        {
            return NULL;
        }
    }
}

