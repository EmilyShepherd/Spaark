<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\View\URLParser;

/**
 * Represents a document that can be surrounded by a
 * StaticTemplate. This would normally be a Page, or
 * a StaticTemplate itself (as templates work in
 * trees).
 *
 * @see HTMLSegment
 * @see Page
 * @see StaticTemplate
 */
abstract class Templateable extends HTMLSegment
{
    /**
     * The templateList is a string representation
     * of the template tree. In the form:
     * top;middle;lower_middle;lowest;
     *
     * Leading or trailing semi-colons are ignored
     */
    protected $templates    = array( );
    
    /**
     * The top sections of this document
     */
    protected $top;
    
    /**
     * the bottom sections of this document
     */
    protected $bottom;
    
    /**
     * Loads the document, adding any templates or widgets
     * as defined
     *
     * @param string $rawHTML The rawHTML to parse
     */
    protected function init($rawHTML = NULL)
    {        
        //buildHTML() loads the HTML and parses it, loading in
        //document headers and replacing XML placeholders
        //with their appropriate templates / widgets
        $this->buildHTML($rawHTML);
        
        //Load the templateable specific aspects of the
        //document
        $this->loadTemplate();
        $this->addIncludes();
    }
    
    /**
     * Adds includes specificed by @js and @css in the
     * document header to the list of head includes,
     * and calculates their cached urls
     */
    private function addIncludes()
    {
        foreach (array('js', 'css') as $type)
        {
            //Does @css or @js exist?
            if (isset($this->response[$type]) && !empty($this->response[$type]))
            {
                //Create list of includes and loop through them
                $list = explode(' ', $this->response[$type]);
                $this->response[$type] = array( );
                
                foreach ($list as $item)
                {
                    if (!$item) continue;
                    
                    $item .= '.' . $type;
                    
                    //If this isn't an empty string or already included,
                    //include it
                    if (!isset($this->response[$type][$item]))
                    {
                        $this->response[$type][$item] =
                            URLParser::parseURL($item);
                    }
                }
            }
        }
    }
    
    /**
     * If a template can be found for this document,
     * it will load it.
     *
     * If this is the top of the template tree, it
     * will load the doctype infomation
     *
     */
    protected function loadTemplate()
    {
        //Calculate the template
        if (isset($this->response['template']))
        {
            if ($this->response['template'] != 'none')
            {
                $template = $this->response['template'];
            }
        }
        else
        {
            $template = dirname('/' . $this->name) . '/template';
        }
        
        //We should only get the template if the JavaScript end
        //doesn't already have it, or the calculated template
        //is itself (to stop infinate loops)
        if (isset($template) && trim($template, '/') != $this->name)
        {
            $templateObj        = new StaticTemplate
            (
                $template,
                $this->response + $this->varsIn
            );
            
            $this->templates    = $templateObj->getTemplates();
            $this->templates[]  = $templateObj;
        }
    }
    
    /**
     * Returns the template list, as a string
     *
     * @return string The template list, as a string
     */
    public function getTemplates()
    {
        return $this->templates;
    }
    
    /**
     * Returns the document's info
     * 
     * @return array The document's info
     * @see $info
     */
    public function getInfo()
    {
        return $this->response;
    }
    
    /**
     * Retuns this template's inline JavaScript
     *
     * @return string This template's inline JavaScript
     */
    public function getScript()
    {
        return $this->script;
    }
}

?>