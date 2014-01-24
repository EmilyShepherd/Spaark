<?php namespace Spaark\Core\View;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Config\Config;

/**
 * Readys CSS for output. This includes:
 *   + Removing comments
 *   + Removing whitespace
 *   + replacing color codes with faster versions
 *   + Replacing links with cached versions
 */
class CSS extends OutputType
{
    /**
     * Takes input css and readies it for output
     *
     * @param string $css The raw CSS to compress
     */
    public function __construct($css)
    {
        $this->output = $css;
        
        //$this->removeComments('@', ';');
        $this->removeComments('\/\*', '\*\/');
        $this->compress();
        $this->removeRGB();
        $this->shortenHashColors();
        $this->parseLinks();
        $this->addRoot();
        $this->addIeClasses();
        
        $this->output = trim($this->output);
        
        $this->addHeader('/*', '*/', ' ', '');
    }
    
    /**
     * Removed whitespace from the CSS
     */
    private function compress()
    {
        $this->output = URLParser::compressWhitespace
        (
            array('{', '}', '(', ')', ',', ':', ';'),
            $this->output
        );
    }
    
    /**
     * Replaces colors in the form rgb(255, 255, 255) with their hex
     * eqivalent (eg #FFFFFF)
     */
    private function removeRGB()
    {
        $this->output = preg_replace_callback
        (
            '/rgb\(([0-9]{1,3}),([0-9]{1,3}),([0-9]{1,3})\)/',
            function ($matches)
            {
                return
                      '#'
                    . dechex(intval($matches[1]))
                    . dechex(intval($matches[2]))
                    . dechex(intval($matches[3]));
            },
            $this->output
        );
    }
    
    /**
     * Shortens 6 diget hex codes to three where possible.
     *
     * Eg. #AA66CC becomes #A6C
     */
    private function shortenHashColors()
    {
        $this->output = preg_replace_callback
        (
            '/(:[^;]*#)([0-9a-fA-F]{6})/',
            function ($matches)
            {
                $hex = strtolower($matches[2]);

                if
                (
                    $hex[0] == $hex[1] &&
                    $hex[2] == $hex[3] &&
                    $hex[4] == $hex[5]
                )
                {
                    return $matches[1] . $hex[1] . $hex[3] . $hex[5];
                }
                else
                {
                    return $matches[1] . $hex;
                }
            },
            $this->output
        );
    }
    
    /**
     * Grabs the links from the css document (normally background
     * images) and replaces them with their cached link, where possible
     *
     * @see URLParser::parseURL()
     */
    private function parseLinks()
    {
        $this->output = preg_replace_callback
        (
            '/url\((\'|")?(.*?)(\'|")?\)/',
            function ($matches)
            {
                return
                      'url'
                    . '('
                    .     $matches[1]
                    .     URLParser::parseURL($matches[2])
                    .     $matches[3]
                    . ')';
            },
            $this->output
        );
    }
    
    /**
     * Replaces {HREF_ROOT} placeholders with the HREF_ROOT value in
     * config.
     */
    private function addRoot()
    {
        $this->output = str_replace
        (
            '{HREF_ROOT}',
            Config::HREF_ROOT(),
            $this->output
        );
    }

    private function addIeClasses()
    {
        $this->output = preg_replace
        (
              '/(article|aside|command|details|dialog|summary|figure|'
            . 'figcaption|footer|header|mark|meter|nav|progress|'
            . 'section)/', '.$1', $this->output
        );
    }
}
