<?php namespace Spaark\Core\View;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Model\HTML\PageBuilder;
use \Spaark\Core\Vars\Vars;
use \Spaark\Core\Output;
use \Spaark\Core\Config\Config;

// {{{ constants

/**
 * When Page::$instruction is set to this, the value of Page::$code is
 * treated as PHP, and is eval'd.
 */
define('CODE_EVAL', 1);

/**
 * When Page::$instruction is set to this, the value of Page::$code is
 * treated as a path, and it is included.
 */
define('CODE_INCLUDE', 2);

// }}}


        ////////////////////////////////////////////////////////


// {{{ Page

/**
 * Stores and outputs a page.
 */
class Page extends \Spaark\Core\Model\Base\Entity
{
    // {{{ static
    
    /**
     * Attempts to load a Page from the cache. If none exist, it will
     * build a new one
     *
     * @param string $name The name of the page to load
     * @param array $varsIn The settings to use
     * @see Page::__construct()
     */
    public static function load($name, $varsIn = array( ))
    {
        if (substr($name, strlen($name) - 1) == '/')
        {
            $name .= 'home';
        }
        
        $name = trim($name, '/');
        
     // try
     // {
     //     $page = Cache::load('page:' . $name);
     //     header('X-Spaark-Page-Cache: used');
     // }
     // catch (CacheMiss $cm)
     // {
            $page = new Page($name);
     // }
        
        $page->run($varsIn);
    }
    
    /**
     * Shortcut to load. Allows pages to be loaded using the syntax:
     *   Page::pagename(args);
     *
     * @param string $name The name of the page to load
     * @param array $args The settings to use
     * @see load()
     */
    public static function __callStatic($name, $args)
    {
        self::load($name, $args);
    }
    
    // }}}
    
    
        ////////////////////////////////////////////////////////
    
    
    // {{{ instance
    
    /**
     * This is the page code
     */
    private $code;
    
    /**
     * The variables to be passed to the page code
     */
    private $varsIn;
    
    /**
     * The JavaScript used by the page. Note: This is set when the page
     * code is run.
     */
    private $script;
    
    /**
     * If the page was not cached, it will need to be built using a
     * PageBuilder, an instance of which will be stored here
     */
    private $builder;

    private $array;
    
    /**
     * Uses a PageBuilder to create the output from the given file
     *
     * @param string $file The name of the file to build
     */
    public function __construct($file)
    {
        $this->builder = new PageBuilder($file);
        
        $this->array            = $this->builder->getInfo();
        $this->array['parents'] = $this->builder->getParents();
        $this->code             = $this->builder->getCode();
        
     // $this->cache();
    }
    
    /**
     * Echos the response, either as a full HTML document, or as JSON,
     * depending on whether this is an AJAX request or not
     *
     * @param array $varsIn The page's arguments
     */
    private function run($varsIn)
    {
        $this->varsIn = $varsIn;
        
        if (!Vars::checkFlag(GET, 'ajax'))
        {
            echo $this->getFullHTML();
        }
        else
        {
            Output::mime('application/json');
            
            echo $this->getJSONResponse();
        }
    }
    
    /**
     * Returns the full HTML for this page
     *
     * @return string The full HTML for this page
     */
    private function getFullHTML()
    {
        return 
              '<!DOCTYPE html>'
            . OutputType::getHeader('<!--', '-->', '<!--', '-->')
            . '<html lang="en">'
            .   '<head>'
            .     '<meta '
            .       'name="viewport" '
            .       'content="'
            .           'width=device-width; '
            .           'initial-scale=1.0; '
            .           'maximum-scale=1.0; '
            .           'minimum-scale=1.0; '
            .           'user-scalable=0;'
            .       '" '
            .     '/>'
            .     $this->runFile()
            .   '</body>'
            . '</html>';
    }
    
    /**
     * If this was an ajax call, we need to return the appropriate
     * information in an AJAX call
     *
     * @return string The json response
     */
    private function getJSONResponse()
    {
        $count = count($this->array['parents']);
        $found = false;
        $list  = '';

        for ($j = 0; $j < $count; $j++)
        {
            $list .=
                  '&template' . $j . '='
                . $this->array['parents'][$j];

            if
            (
                !$found &&
                !Vars::checkValue
                (
                    GET,
                    'template' . $j,
                    $this->array['parents'][$j]
                )
            )
            {
                $i     = $j + 1;
                $found = true;
                if (isset($this->array['parents'][$j - 1]))
                {
                    $element = $this->array['parents'][$j - 1];
                }
            }
        }

        if (!$found) $i = $count + 1;

        $element =
            isset($this->array['parents'][$i - 2])
              ? 'template_' . $this->array['parents'][$i - 2]
              : 'spaark_page';

        return json_encode(array
        (
             'title'    => $this->array['document.title'],
             'content'  => $this->runFile($i),
             'template' => $list,
             'element'  => $element,
             'script'   => $this->script,
             'css'      => array_values($this->array['css']),
             'js'       => array_values(array_reverse($this->array['js'])),
             'statics'  => $this->array['statics']
        ));
    }
    
    /**
     * Runs the page code, returning it's HTML
     *
     * @param int $i The template level to use
     * @return string The HTML 
     * @see getJSONResponse() for the calculation of $i
     */
    private function runFile($__i = 0)
    {
        Output::ob_clean();
        
        extract($this->varsIn);
        
        if ($this->code)
        {
            eval($this->code);
        }
        else
        {
            include $this->array['path'];
        }
        
        $html = ob_get_contents();
        
        Output::ob_clean();
        
        return $html;
    }
    
    /**
     * Caches this Page for this request, if the page is set as cachable
     */
    protected function _cache()
    {
        if (!isset($this->array['ttl']) || $this->array['ttl'] == -1)
        {
            return;
        }
        
        $this->setTTL($this->array['ttl']);
        $this->array['path'] =
              $this->config->config['cache']['path']
            . str_replace('/', '_', $this->builder->getName())
            . '.page';
        
        file_put_contents
        (
            $this->array['path'],
            '<?php ' . $this->code
        );
        
        Cache::save('page:' . $this->builder->getName(), $this);
    }
    
    /**
     * Returns true if this cache is valid
     *
     * This performs the same function as CacheEntry's valid(), except
     * this cleans up the extra file if it is not valid
     *
     * @return bool true if this cache is valid
     * @see CacheEntry::valid()
     */
    public function valid()
    {
        if
        (
            (!$this->code && !file_exists($this->array['path'])) ||
            (!parent::valid())
        )
        {
            if (file_exists($this->array['path']))
            {
                unlink($this->array['path']);
            }
            
            return false;
        }
        else
        {
            return true;
        }
    }
    
    // }}}
}

// }}}

?>