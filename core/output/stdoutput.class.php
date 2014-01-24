<?php namespace Spaark\Core\Output;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Output;
use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Instance;
use \Spaark\Core\View\Page;
use \Spaark\Core\Config\Config;
use \Spaark\Core\Error\NotFoundException;

/**
 * Handles the standard output aspects of Spaark. Things like images,
 * css, js, static html, error pages and cache controlled resources.
 */
class StdOutput extends \Spaark\Core\Base\Controller
{
    public function page()
    {
        $this->routeCacheTTL = 300;

        $page = Instance::getRequest();

        if (basename($page) == 'home') return;

        try
        {
            Page::load(Instance::getRequest());
        }
        catch (NotFoundException $nfe)
        {
            Page::load(Instance::getRequest() . '/home');
        }
    }

    /**
     * Compresses the css file and replaces links, then outputs its
     * contents
     *
     * @response text/css
     * @ttl 0
     */
    public function css()
    {
        Output::mime('text/css');
        Output::ttl(0);

        $css = new \Spaark\Core\View\CSS
        (
            file_get_contents
            (
                Config::CSS_PATH() . trim(Instance::getRequest(), '/')
            )
        );

        echo $css;
    }
    
    /**
     * @mime application/javascript
     * @ttl 0
     */
    public function js()
    {
        $path = urldecode(Config::JS_PATH() . Instance::getRequest());
        if (!file_exists($path)) return;
        
        Output::mime('application/javascript');
        Output::ttl(0);
        
        $js = new \Spaark\Core\View\JavaScript(file_get_contents($path));
        
        echo $js;
    }
    
    /**
     * Outputs an image and sets the appropriate Content-Type response
     * header
     */
    public function image()
    {
        $path = urldecode(Config::IMAGE_PATH() . Instance::getRequest());
        if (!file_exists($path)) return;
        
        $ext = pathinfo(Instance::getRequest(), PATHINFO_EXTENSION);

        Output::mime('image/' . ($ext == 'jpg' ? 'jpeg' : $ext));
        Output::ttl(INDEFINITE);

        echo file_get_contents($path);
    }
    
    public function favicon()
    {
        Output::mime('image/x-icon');
        
        echo file_get_contents(SPAARK_PATH . 'default/images/icon.ico');
    }

    /**
     * Serves cached content, with a long expires time (roughly 10
     * years)
     */
    public function cache()
    {
        $req   = pathinfo(Instance::getRequest());
        $req   = pathinfo(rtrim($req['dirname'], '/') . '/' . $req['filename']);
        $url   = rtrim($req['dirname'], '/') . '/' . $req['filename'];
        $etag  = $req['extension'];
        
        try
        {
            $cache = Cache::load('output', $url);
        }
        catch (CacheMiss $cm)
        {
            header('location: ' . $url);
            exit;
        }
        
        if ($cache->etag != $etag)
        {
            header('location: ' . $url . '.' . $cache->etag . '.cache');
            exit;
        }
        
        header('cache-control: public, max-age=320000000');
        $cache->send();
        exit;
    }

    public function humans()
    {
        Output::mime('text/plain');

        if (file_exists(Config::APP_ROOT() . 'txt/humans.txt'))
        {
            echo file_get_contents
            (
                Config::APP_ROOT() . 'txt/humans.txt'
            );
        }
        elseif ($this->config->humans)
        {
            foreach ($this->config->humans as $title => $entries)
            {
                echo '/* ' . strtoupper($title) . ' */' . "\r\n";

                foreach ($entries as $entry)
                {
                    foreach ($entry as $key => $value)
                    {
                        echo '    ' .  $key . ': ' . $value . "\r\n";
                    }

                    echo "\r\n";
                }
            }
        }
        else
        {
            echo str_replace
            (
                '{dev}',
                $this->config->config->app['admin'],
                file_get_contents
                (
                    SPAARK_PATH . 'default/txt/humans.txt'
                )
            );
        }

        exit;
    }

    /**
     * Displays the 404 page, 404.html. Spaark provides a generic one
     * for you, but you can make your own to override it (call it
     * 404.html and put it in your app's HTML folder).
     *
     * The normal templating rules apply to 404.html
     */
    public function error404()
    {
        Cache::ignoreBucket();
        Output::status(NOT_FOUND);
        
        //$this->page->fullPage();
        
        Page::load
        (
            '404',
            array
            (
                'url'       => Instance::getRequest(),
                'signature' => Config::APP() . ' on ' . SIGNATURE
            )
        );
    }
    
    /**
     * Displays the SystemException stack trace in a neat format, and
     * within your site's template.
     */
    public function exception($e)
    {
        Cache::ignoreBucket();
        
        Output::status(ERROR);
        
        Page::load
        (
            'error',
            array
            (
                'error'     => $e, //print_r($e->getTrace(), true),
                'signature' => SIGNATURE
            )
        );
    }
}

?>