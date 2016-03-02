<?php namespace Spaark\Core;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

//use Spaark\Core\Config\Config as OldConfig;
use Spaark\Core\Model\Config;
use Spaark\Core\Cache\Cache;
use Spaark\Core\Cache\CacheMiss;

/**
 * Handles a request, either by using its cached Output, or by running
 * it through the Router
 */
class Instance
{
    /**
     * This is the URI we are handling, minus the HREF_ROOT of the site.
     */
    private static $uri;
    
    /**
     * The Router object for this Request
     */
    private static $router;

    public static function bootstrap($site, $uri = NULL)
    {
        ClassLoader :: init();
        $n = new Instance($site, $uri);
    }
    
    /**
     * Sets up Spaark's environment and attempts to handle the request.
     *
     * This is normally either:
     *   + From cache
     *   + Built
     *
     * In the special case that there is an _escaped_fragment_ GET
     * parameter present, Spaark will automatically redirect to the
     * correct location (this is Google's AJAX Crawling Protocol). This
     * is handled by self::handleFragment().
     *
     * @param string $app The application file
     * @param string $uri The request URI
     * @see Instance:handleFragment()
     */
    public function __construct($site, $uri = NULL)
    {
        Config :: init($site);

        // TODO: This should run before the Config is loaded really
        self   :: handleFragment();
        self   :: buildURI($uri);
        self   :: handleSlashes();
        
     // self   :: loadFromCache();
        ClassLoader :: appInit();
        self   :: init();
        self   :: loadFromRoute();
    }

    /**
     * Handles Google's AJAX Crawling Protocol
     *
     * If _escaped_fragment_ is passed as GET variable, the entire
     * request is deemed wrong. The correct response is taken from the
     * value of _escaped_fragment_. This is because when an IE user
     * follows an AJAX link, #!/link/path is added to their URL. If they
     * were previously on /other/path, their full relative URL becomes
     * /other/path#!/link/path. If they post a link to this, the hash
     * fragment is the only relavent part of the link. We inform Google
     * of this via 301 code.
     *
     * Please Note: Although the protocol states that the
     * _escaped_fragement_ will always appear at the end of the query
     * string, Spaark will treat this as an AJAX Crawl if it is found
     * ANYWHERE.
     *
     * @see https://developers.google.com/webmasters/ajax-crawling/docs/specification
     */
    private static function handleFragment()
    {
        if (isset($_GET['_escaped_fragment_']))
        {
            self::redirect
            (
                rtrim($_GET['_escaped_fragment_'],  '/'),
                'Found Fragment'
            );
        }
    }

    private static function handleSlashes()
    {
        if (self::$uri != '/' && substr(self::$uri, -1) == '/')
        {
            $uri = rtrim(self::$uri, '/');
            if ($_SERVER['QUERY_STRING'])
            {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }

            self::redirect
            (
                $uri,
                'Bad Trailing Slash'
            );
        }
    }

    private static function redirect($to, $reason)
    {
        header('HTTP/1.1 301 ' . $reason);
        header('Location: ' . $to);
        header('X-Powered-By: spaark/' . VERSION);
        exit;
    }
    
    private static function buildURI($uri)
    {
        if (!$uri)
        {
            $uri = self::findURI();
        }
        
        $pos = strpos($uri, '?');
        $uri =
              '/'
            . substr
              (
                  $uri,
                  strlen(Config::getConf('href_root'))
              );

        if ($pos !== false)
        {
            $uri = substr($uri, 0, $pos);
        }
        
        self::$uri = $uri;
    }
    
    /**
     * Trys to calculate the current URI.
     *
     * If Spaark is running on an Apache web server, it will use the
     * value of $_SERVER['REQUEST_URI']. Otherwuse it will either use
     * the value of $_SERVER['REQUEST_URI'] if it exists, or the
     * combination of $_SERVER['PHP_SELF'] with
     * $_SERVER['QUERY_STRING'].
     */
    private static function findURI()
    {
        if (isset($_SERVER['SERVER_SOFTWARE']))
        {
            if (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') === 0)
            {
                return $_SERVER['REQUEST_URI'];
            }
        }

        if
        (
            isset($_SERVER['REQUEST_URI']) &&
            $_SERVER['REQUEST_URI']
        )
        {
            return $_SERVER['REQUEST_URI'];
        }
        else
        {
            $uri = $_SERVER['PHP_SELF'];
            if (isset($_SERVER['QUERY_STRING']))
            {
                $uri .= $_SERVER['QUERY_STRING'];
            }
            return $uri;
        }
    }
    
    /**
     * Returns the Request object
     *
     * @return Request The Request object
     */
    public static function getRequest()
    {
        return self::$uri;
    }
    
    /**
     * Initializes Spaark for use with third party code.
     *
     * It does this by including the second batch of essential files,
     * initialising the output and error listeners and boxing GET
     * variables. It will also set a default timezone one isn't found.
     */
    private static function init()
    {
        ob_start();

        if ($ts = Config::getConf('timezone'))
        {
            date_default_timezone_set($ts);
        }
        
        Error\Error :: init();
        Vars\Vars   :: init();
        register_shutdown_function('\Spaark\Core\Instance::shutdown');
    //  Cache       :: init(self::$uri);
    }
    
    /**
     * Attempts to load an Output object from this Request's cache.
     *
     * If one is found, it will send it and exit. This is called by
     * Instance; if this doesn't exit, Instance will load the full
     * load of code, and call Request::loadFromRoute()
     *
     * @see Instance::start()
     * @see Instance::loadFrameworkFull()
     * @see Request::loadFromRoute()
     */
    private static function loadFromCache()
    {
        try
        {
            if (Cache::load('output', self::$uri)->send())
            {
                exit;
            }
        }
        catch (CacheMiss $cm) {}
    }
    
    /**
     * Handles the request, by running it through the Router.
     *
     * It'll try to use the cached route, if one exists.
     *
     * It will register the shutdown listener to handle when the Output
     * is ready.
     *
     * @see Request::shutdown()
     */
    private static function loadFromRoute()
    {
        self::$router = new Router(self::$uri);
        self::$router->route();
    }
    
    /**
     * Handles the shutdown of Spaark.
     *
     * Sends the output if one exists that hasn't been sent, caches the
     * Output if approrpiate and calls the shutdown method of the
     * Router.
     *
     * @see Router::shutdown()
     */
    public static function shutdown()
    {
        Output::fromOutput();
        
        $output = Output::getObj();
        $router = self::$router;

        if (!$output->send())
        {
            Error::unexpectedEnd();
        }
        
        Cache::output($output);

        $router->shutdown();
    }
}

?>