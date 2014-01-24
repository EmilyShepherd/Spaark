<?php namespace Spaark\Core\Model\XML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Abstract class for classes that can handle tags as read by the
 * HTMLSegment.
 *
 * @see HTMLSegment::handle_element()
 */
abstract class ElementHandler
{
    /**
     * The parent XMLDocument
     */
    protected $handler;
    
    /**
     * Constructor
     *
     * @param XMLParser $handler The parent XMLParser
     */
    public function __construct(XMLparser $handler)
    {
        $this->handler = $handler;
    }
    
    /**
     * This method is called by the XMLParser when the element this
     * handles is found
     *
     * @param string $tag The tag that was found
     * @param string $attrs String of attributes
     * @param string $content The HTML / text within the element
     * @return mixed A replacement string if required, true otherwise
     */
    abstract public function parse($tag, $attrs, $content);
    
    /**
     * Writes out the element, as correct HTML
     *
     * @param string $tag The name of the tag (eg "img")
     * @param string $attrs The attributes as a string
     * @param array $attArr Other attributes as an array
     * @param string $content HTML / text to go inside the element. If
     *     false, the element will be closed with a />
     * @return string The full element
     */
    protected function build($tag, $attrs, $attrArr, $content)
    {
        $newAttrs = '';
        
        foreach ($attrArr as $key => $value)
        {
            $newAttrs .= $key . '="' . $value . '" ';
        }
        
        $attrs = trim($newAttrs . $attrs);
        
        return
              '<' . $tag . ($attrs ? ' ' . $attrs : '')
            . (
                $content === false
                  ? '/>'
                  : '>'  . $content
                  . '</' . $tag . '>'
              );
    }
    
    /**
     * Attempts to find the given attribute in an attribute string
     *
     * @param string $string The string of attributes
     * @param string $attr The attribute to find
     * @param bool $remove If true, this attribute will be removed from
     *     the string (useful if you intend to change its value)
     * @return string The value of the attribute if found. Otherwise ''
     */
    protected function getAttr(&$string, $attr, $remove)
    {
        if
        (
            preg_match
            (
                '/' . $attr . ' *= *(\'|")([^\1]*?)\1/s',
                $string,
                $attrs,
                PREG_OFFSET_CAPTURE
            )
        )
        {
            if ($remove)
            {
                $offset = $attrs[0][1];

                $string =
                      substr($string, 0, $offset)
                    . substr($string, $offset + strlen($attrs[0][0]));
            }
            
            return $attrs[2][0];
        }
        else
        {
            return '';
        }
    }
    
    protected function getAllAttrs($string)
    {
        preg_match_all
        (
            '/([a-zA-Z0-9]+) *= *(\'|")([^\1]*?)\2/s',
            $string,
            $attrs
        );
        
        $ret = array( );
        
        if (isset($attrs[0]))
        {
            foreach ($attrs[0] as $i => $val)
            {
                $ret[$attrs[1][$i]] = $attrs[3][$i];
            }
        }
        
        return $ret;
    }
}

?>