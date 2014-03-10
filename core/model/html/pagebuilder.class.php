<?php namespace Spaark\Core\Model\HTML;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Config\Config;


/**
 * Represents a full page. It can respond in normal HTML, or AJAX
 */
class PageBuilder extends Templateable
{
    /**
     * The element that the HTML will be put in, if this is responding
     * to an AJAX request
     */
    private $element  = 'spaark_page';
    
    /**
     * If true, this will ignore the AJAX request's element directive
     * and force it to take up the whole page. Useful for responding
     * in an unexpected way (e.g. showing an Exception)
     */
    private $fullPage = false;
    
    /**
     * The PHP code generated by this class - this allows the file to be
     * cached and then executed straight from disk
     */
    private $code;
    
    /**
     * This array contains the names of the templates that this document
     * inherits from
     */
    private $parents = array( );
    
    /**
     * Default values for all the settings this class expects
     */
    private $defaultResponse = array
    (
        'title'            => 'Untitled Spaark Page',
        'document.title'   => '{title}',
        'window.title'     => '{document.title}',
        'description'      => '',
        'keywords'         => '',
        'auth'             => 'none',
        'ttl'              => -1,
        'meta.description' => '{description}',
        'meta.keywords'    => '{keywords}',
        'og.title'         => '{document.title}',
        'og.type'          => 'website',
        'og.site_name'     => '{APP}',
        'og.url'           => '{request}',
        'og.description'   => '{description}',
        'css'              => array( ),
        'js'               => array( ),
        'meta'             => array( ),
        'other'            => array( )
    );
    
    private $finishedResponse = array( );
    
    /**
     * Sets fullPage to be true
     */
    public function fullPage()
    {
        $this->fullPage = true;
    }
    
    /**
     * Loads the given file
     *
     * @param string $name The name of the file
     * @param array $replace Used for $varsIn
     */
    public function __construct($name)
    {
        $this->setName($name);
        
        $this->init($this->loadFromFile());
        
        $this->buildFile();
    }
    
    /**
     * Returns the part of the page's head. This includes the title,
     * script and meta tags. It also creates the body opening tags (with
     * IE conditionals)
     *
     * @return string The document head
     */
    private function drawHead()
    {
        return
                '<title>' . $this->response['window.title'] . '</title>'
            .   $this->drawIncludes()
            . '</head>'
            . '<body>'
            .   '<div id="spaark_page">';
    }
    
    /**
     * Draws the resource includes for the full document
     *
     * @return string The resource includes
     */
    private function drawIncludes()
    {
        $return = '';
        $i      = 0;
        
        foreach ($this->response['css'] as $raw => $cache)
        {
            if ($cache)
            {
                $return .=
                      '<link '
                    .     'rel="stylesheet" '
                    .     'id="s_' . $i++ . '" '
                    .     'href="' . $cache . '" '
                    .     'media="all"'
                    . '/>';
            }
        }
        foreach ($this->response['meta'] as $name => $value)
        {
            if (!is_int($name))
            {
                $return .=
                      '<meta '
                    .     'name="'    . $name  . '" '
                    .     'content="' . $value . '"'
                    . '/>';
            }
        }
        $return .= implode('', array_keys($this->response['other']));
        
        return $return;
    }
    
    /**
     * Returns the document script tags
     *
     * These are generately separately from the other resources, because
     * they scripts appear at the bottom of the page to prevent blocking
     * while waiting from them to load.
     *
     * @return string The script includes
     */
    private function drawScriptTags()
    {
        $return = '';
        
        foreach (array_reverse($this->response['js']) as $raw => $cache)
        {
            if ($cache != '' && $cache != '/Array.js')
            {
                $return .= '<script src="' . $cache . '"></script>';
            }
        }
        
        return $return;
    }
    
    /**
     * Creates the PHP for this page by combining this document's
     * HTML with the HTML from the page's template.
     *
     * This function creates compact PHP code and is, therefore, one of
     * the ugliest I have ever written.
     *
     * @return string PHP containing the full HTML
     */
    private function buildFile()
    {
        $top         = '';
        $bottom      = '';
        $templateStr = '';
        $script      = $this->script;
        $base        =
              '$this->script=\'' . str_replace('\'', '\\\'', $this->script) . '\';'
            . 'return;';
        $response    = $this->response;
        
        $count = count($this->templates);

        foreach (array_reverse($this->templates) as $i => $template)
        {
            $i = $count - $i;
            
            $response  = $this->array_join
            (
                $response,
                $template->getInfo()
            );
            $this->parents[] = $template->getName();
            $script          = $template->getScript() . $script;
            
            $templateStr     =
                  '&template' . ($i - 1) . '=' . $template->getName()
                . $templateStr;

            $top            =
                  'case ' . $i . ':?>'
                . $template->getTop()
                . '<?php '
                . $top;

            $bottom         .=
                  '?>'
                . $template->getBottom()
                . '<?php '
                . 'if($__i==' . $i . ')'
                . '{'
                .     '$this->script=\'' . str_replace('\'', '\\\'', $script) . '\';'
                .     'return;'
                . '}';
        }
        
        $this->parents  = array_reverse($this->parents);
        $this->response = $this->array_join
        (
            $response,
            $this->defaultResponse
        );
        $this->finishResponse();
        $this->response            = $this->finishedResponse;
        $this->response['statics'] = array( );
        $this->response['css']     = $this->response['css'] ?: array( );
        $this->response['js']      = $this->response['js']  ?: array( );
        
        $this->code =
              'switch($__i)'
            . '{'
            .     'case 0:?>'
            .     $this->drawHead()
            .     '<?php '
            . $this->replacevars
              (
                        $top
                  . '}?>'
                  . $this->html
                  . '<?php '
                  . 'if($__i==' . ($count + 1) . '){' . $base . '}'
                  . $bottom
              )
            . '?>'
            . '</div>'
            . '<script src="/js.spaark"></script>'
            . $this->drawScriptTags()
            . '<script>'
            .   's'
            .   '('
            .       '\'' . $this->config->app['href_root'] . '\','
            .       '\'' . $templateStr        . '\','
            .       json_encode(array_reverse(array_values($this->response['js']))) . ','
            .       json_encode(array_values($this->response['css']))
            .   ');'
            .   $script
            .   (
                    isset($this->response['ga'])
                      ? 'ga(\'' . $this->response['ga'] . '\');'
                      : ''
                )
            . '</script>';
    }
    
    private function finishResponse()
    {
        foreach ($this->response as $name => $val)
        {
            if (is_array($val))
            {
                $this->finishedResponse[$name] = $val;
            }
            elseif (!isset($this->finishedResponse[$name]))
            {
                $this->doResponseItem($name, $val);
            }
        }
    }
    
    private function doResponseItem($name, $value)
    {
        $this->finishedResponse[$name] = preg_replace_callback
        (
            '/\{(.*?)\}/',
            array($this, 'replacePlaceholder'),
            $value
        );
        
        return $this->finishedResponse[$name];
    }
    
    private function replacePlaceholder($matches)
    {
        if (!isset($this->finishedResponse[$matches[1]]))
        {
            if (!isset($this->response[$matches[1]]))
            {
                return $matches[0];
            }
            else
            {
                return $this->doResponseItem
                (
                    $matches[1],
                    $this->response[$matches[1]]
                );
            }
        }
        
        return $this->finishedResponse[$matches[1]];
    }
    
    /**
     * Returns the templates this document inherits from
     *
     * @return array The templates this document inherits from
     */
    public function getParents()
    {
        return $this->parents;
    }
    
    /**
     * The PHP code to run this document
     *
     * @return string The PHP code to run this document
     */
    public function getCode()
    {
        return $this->code;
    }
}

?>