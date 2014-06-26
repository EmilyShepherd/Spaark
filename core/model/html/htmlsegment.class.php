<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Config\Config;
use \Spaark\Core\Config\ConfigReader;
use \Spaark\Core\Model\XML\XMLParser;
use \Spaark\Core\View\URLParser;
use \Spaark\Core\Error\NotFoundException;

/**
 * Parses HTML documents / fragments to implement Spaark functionality
 */
abstract class HTMLSegment extends \Spaark\Core\Model\Base\Entity
{
    /**
     * The response variables that this HTMLSegment defined at the top,
     * in the form {@}var. They are for things like title / css scripts
     */
    protected $response = array( );
    
    /**
     * The HTML output of this HTMLSegment
     */
    protected $html   = '';
    
    /**
     * The variables that should be used in this HTMLSegment,
     * placeholders formated like {{key}} will be replace with that
     * key's value in this array
     */
    protected $varsIn = array( );
    
    protected $statics = array( );
    
    /**
     * The name of this HTMLSegment, used to load the file and name
     * forms
     */
    protected $name;
    
    /**
     * The file extension used for this HTMLSegment
     */
    protected $extension = '.html';
    
    /**
     * The inline script used in this HTMLSegment, this is removed
     * from the HTML and sent separately for AJAX requests, or appended
     * to the bottom for non-AJAX requests
     */
    protected $script = '';
    
    protected $static = true;
    
    const CONF = 'htmlpath';
     
    /**
     * Sets the name of this HTMLSegment and prepopulates the value of
     * $path with Config::HTML_PATH. You may change this value before
     * using load()
     *
     * @return string The name
     */
    public function setName($name)
    {
        return $this->name = trim($name, '/');
    }
    
    /**
     * Returns the name of this HTMLSegment
     *
     * @return string The name of this HTMLSegment
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Loads the rawHTML of the the file located at:
     * $path . $name . $extension.
     *
     * @return string The rawHTML at that file
     * @throws NotFoundException if the file doesn't exist
     */
    protected function loadFromFile()
    {
        $path = $this->config->path . $this->name . '.html';
        
        if ($output = $this->_load($path))
        {
            return $output;
        }
        
        $output = $this->_load(SPAARK_PATH . 'default/' . $path);
        if ($output)
        {
            return $output;
        }
        
        throw new NotFoundException($path);
    }
    
    /**
     * Used by load() to load a file. Loads the content of the given
     * path and returns the rawHTML
     *
     * @param string $path The path of the file
     * @return mixed false if the file doesn't exist.
     */
    protected function _load($path)
    {
        if (!file_exists($path))
        {
            return false;
        }
        
        //Load the file
        return file_get_contents($path);
    }
    
    /**
     * This method calls the worker methods to parse the HTMLSegment.
     * The output is put into the $output array.
     *
     * Files are parsed as follows:
     *  + Place holders are replaced using $varsIn
     *  + The config headers are parsed
     *  + HTML comments are removed
     *  + Non-XML compliant tags like <br> are converted to <br />
     *  + Non-XML compliant attribute tags are fixed
     *  + Inline scripts are removed and stored in $script
     *  + The document is parsed as XML:
     *    - Box and template code is put in
     *    - StaticTemplates are calculated
     *    - Widgets are processed
     *    - Script links are removed, as they are sent differently for
     *      AJAX requests
     *    - Resource links are changed to cached versions
     *    - Spaark form functionality is added
     *    - Links are replaced with AJAX page load calls
     *
     * @param string $html The rawHTML to parse
     * @see ConfigReader
     * @see fixAttributes()
     * @see convertTags()
     * @see replaceVars()
     * @see XMLParse()
     */
    protected function buildHTML($html)
    {
        $conf = new ConfigReader($html, FILE_HEAD, false);
        $html = $conf->getData();
        $this->response = $conf->getArray();
       
        //Take out comments, because I don't like them
        $html = preg_replace('/<!--(.|\s)*?-->/', '', $html);
        
        $html = preg_replace('/<\?(?!php|=)\s?/', '<?php ', $html);

        $this->fixAttributes($html);
    
        //Change html non-closing tags to XML ones "<br>" -> "<br />"
        $this->convertTags($html);
       
        //XML parse the document
        $this->XMLParse($html);
    }
    
    /**
     * Converts non-XML-compliant attribute "flags" into acceptable
     * syntax.
     * 
     * Eg. '<input disabled/>' becomes '<input disabled="disabled"/>'
     *
     * @param string $html The html to fix
     */
    public function fixAttributes(&$html)
    {
        do
        {
            $last = $html;
// TODO: Broken            var_dump($html);exit;
            $html = preg_replace_callback
            (
                '/<([^>]*)^(=\"\s)(selected|checked|disabled|required|hidden)(\s|>)/',
                function ($matches)
                {
                    return
                          '<'
                        . $matches[1]
                        . $matches[2] . '="' . $matches[2] . '"'
                        . $matches[4];
                },
                $html
            );
        }
        while ($last != $html);
    }
    
    /**
     * Converts non-XML-compliant HTML tags into acceptable XML
     * alternatives. E.g. "<br>" becomes "<br />".
     *
     * NB: This isn't intelligent, it will only fix unclosed tags for
     * HTML elements that are known to not need them.
     *
     * @param string $html The non-compliant HTML to fix
     */
    private function convertTags(&$html)
    {
        $html = preg_replace_callback
        (
              '/<'
            . '('
            .     'br|col|area|base|embed|source|param|hr|'
            .     'img|link|meta|input|command'
            . ')'
            . '(.{0}|[\s\t\r\n]+(.*?[^\-\?])?)>/',
              function ($matches)
              {
                  if
                  (
                      strlen($matches[2]) == 0                    ||
                      $matches[2][strlen($matches[2]) - 1] != '/'
                  )
                  {
                    return '<' . $matches[1] . $matches[2] . '/>';
                  }
                  else
                  {
                      return $matches[0];
                  }
              },
              $html
        );
    }
    
    private function XMLParse($html)
    {
        $xml = new XMLParser($html);
        
        $xml->setNS('\Spaark\Core\Model\HTML\Handlers');
        
        $xml->setHandler('a',        'AHandler');
        $xml->setHandler('img',      'ImgHandler');
        $xml->setHandler('script',   'ScriptHandler');
        $xml->setHandler('form',     'FormHandler');
        $xml->setHandler('box',      'BoxHandler');
        $xml->setHandler('fragment', 'FragmentHandler');
        $xml->setHandler
        (
              'article|aside|command|details|dialog|summary|figure|'
            . 'figcaption|footer|header|mark|meter|nav|progress|'
            . 'section',
            'HTML5Handler',
            true
        );
        
        $xml->name       = $this->name . $this->extension;
        $xml->validators = array( );
        
        $html = $xml->parse();
        
        $this->response['validators'] = $xml->validators;
        $this->html   = URLParser::compressWhitespace
        (
            array( ), $html
        );
        foreach ($xml->validators as $type => $validator)
        {
            $xml->script .=
                  'h'
                . '('
                .     '"' . $type . '",'
                .     $validator
                . ');';
        }
        $this->script = URLParser::compressWhitespace
        (
            array
            (
                '{', '}', '(', ')', '+', '-'
            ),
            $xml->script
        );
        $this->response['js'] =
            isset($this->response['js'])
              ? $this->response['js'] . $xml->js
              : $xml->js;
    }
    
    protected function replaceVars($html)
    {
        return preg_replace_callback
        (
            '/(\\\\?)(@?){([a-zA-Z0-9\._\[\'"\]]+?)}/',
            array($this, 'replaceVarsCB'),
            $html
        );
    }
    
    protected function replaceVarsCB($matches)
    {
        $name = $matches[3];
        
        if ($matches[1] == '\\')
        {
            return substr($matches[0], 1);
        }
        else if ($matches[2] == '@')
        {
            $val =
                isset($this->response[$name])
                  ? $this->response[$name]
                  : '';
            
            $this->response['statics'][$name] = $val;
            
            return
                  '<span id="spaark_' . $name . '">'
                .   $val
                . '</span>';
        }
        else
        {
            $this->static = false;
            
            $parts = explode('.', $name);
            
            if (in_array($parts[0], array('SERVER', 'REMOTE', 'HTTP')))
            {
                $name = '_SERVER[\'' . $parts[0] . '_' . $parts[1] . '\']';
            }
            else
            {
                $name = implode('->', $parts);
            }

            return
                  '<?php '
                .   'echo $' . $name . ';'
                . '?>';
        }
    }
    
    protected function array_join($arr1, $arr2)
    {
        foreach ($arr2 as $index => $value)
        {
            if (!isset($arr1[$index]))
            {
                $arr1[$index] = $value;
            }
            elseif (is_array($value))
            {
                $arr1[$index] = $this->array_join($arr1[$index], $value);
            }
        }
        
        return $arr1;
    }
	
    public function isStatic()
    {
        return $this->static;
    }
}

?>