<?php namespace Spaark\Core;

use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Config\Config;
use \Spaark\Core\Error\NotFoundException;
use \Spaark\Core\Cache\CacheEntry;

/**
 * Calculates the correct route for a given request
 */
class Router
{
    /**
     * The request
     */
    protected $request;

    /**
     * The route (as a string) that was decided on
     * An instance of the route
     */
    protected $route;
    protected $obj;
    
    protected $cache = false;

    public function __construct($request)
    {
        $this->request = (string)$request;
    }

    public function route()
    {
        $routes = array
        (
            array('\Spaark\Core\Output\SpaarkOutput', 'router', '\.spaark$'),
            array('\Spaark\Core\Output\StdOutput', 'favicon', '^/favicon.ico$'),
            array('\Spaark\Core\Output\StdOutput', 'cache', '\.cache$'),
            array('\Spaark\Core\Output\StdOutput', 'css', '\.css$'),
            array('\Spaark\Core\Output\StdOutput', 'js', '\.js$'),
            array('\Spaark\Core\Output\StdOutput', 'humans', '^/humans.txt$'),
            array('\Spaark\Core\Output\StdOutput', 'image', '\.(png|jpg|jpeg|gif|bmp)$'),
            array('\Spaark\Core\Output\Router', 'method'),
            array('\Spaark\Core\Output\StdOutput', 'page'),
            array('\Spaark\Core\Output\Router', 'args'),
            array('\Spaark\Core\Output\StdOutput', 'error404')
        );
        
        try
        {
            $this->runRoute(Cache::route()->route);
        }
        catch (CacheMiss $cm) {}
        
        try
        {
            if (!Config::IGNORE_ROUTE_TREE())
            {
                $this->traceRouteTree();
            }
        }
        catch (CacheMiss $cm) {}
        
        header('X-Spaark-Route-Debug: Route Tree Fail');
        
        $this->cache = true;
        $this->_route($routes);
        
        throw new \Exception
        (
            'Incomplete routing table'
        );
    }
    
    private function traceRouteTree()
    {
        $croutes         = Cache::load('routes', 'global');
        $this->routeTree = $croutes->routes;
        $this->routeNode =& $this->routeTree;
        $this->parts     = explode('/', trim($this->request, '/'));
        $this->stack     = array( );
        
        for ($i = 0; $i < count($this->parts); $i++)
        {
            if (isset($this->routeNode[$this->parts[$i]]))
            {
                array_push($this->stack, $this->routeNode);
                
                $this->routeNode = &$this->routeNode[$this->parts[$i]];
            }
            else
            {
                $this->tryRouteNode($i);
                break;
            }
        }
        
        //Fail, let's go up and see what we can do
        while (!empty($this->stack))
        {
            $this->routeNode = array_pop($this->stack);
            
            $this->tryRouteNode(--$i);
        }
    }
    
    private function tryRouteNode($i)
    {
        if (isset($this->routeNode['/' . $_SERVER['REQUEST_METHOD']]))
        {
            $this->runRouteNode
            (
                $i,
                $this->routeNode['/' . $_SERVER['REQUEST_METHOD']]
            );
        }
        
        if (isset($this->routeNode['/*']))
        {
            $this->runRouteNode($i, $this->routeNode['/*']);
        }
    }
    
    private function runRouteNode($i, $node)
    {
        $argCount  = count($this->parts) - $i;
        $castCount = count($node['casts']);
        $max       = $castCount + count($node['optcasts']);
        $args      = array( );
        
        if
        (
            $argCount >= $castCount &&
            $argCount <= $max
        )
        {
            for ($j = 0; $j < count($this->parts) - $i; $j++)
            {
                if ($j >= $castCount)
                {
                    $cast = $node['optcasts'][$j - $castCount];
                }
                else
                {
                    $cast = $node['casts'][$j];
                }
                
                if (!$cast)
                {
                    $args[] = $this->parts[$j];
                }
                else
                {
                    if (!$cast::validate($this->parts[$j])) return;
                    
                    $args[] = new $cast($this->parts[$j]);
                }
            }
            
            $this->runRoute
            (
                array($node['class'], $node['method']),
                $args
            );
        }
    }

    /**
     * _route
     *
     * Routes an item
     *
     * If the item is an array, it calls runRouteTests($route),
     * otherwise it calls loadRoute($route)
     *
     * @param $route The route
     */
    protected function _route($routes)
    {
        foreach ($routes as $route)
        {
            if (!isset($route[2]))
            {
                $this->runRoute($route);
            }
            else
            {
                if (!isset($route[3]))
                {
                    $route[3] = 'i';
                }
                
                $preg = '#' . $route[2] . '#' . $route[3];
                
                if (preg_match($preg, $this->request))
                {
                    $this->runRoute($route);
                }
            }
        }
    }

    private function runRoute($route, $args = array( ))
    {
        try
        {
            Output::init();
            
            ClassLoader::load($route[0]);
            
            if (!is_callable(array($route[0], $route[1]))) return;
            
            $this->obj   = new $route[0]();
            $this->route = $route;
            
            call_user_func_array
            (
                array($this->obj, $route[1]),
                $args
            );

            Output::attemptEnd();
        }
        catch (NotFoundException $nfe)
        {
            return;
        }
        catch (UnauthorisedException $ue)
        {
            $this->runRoute($ue->getLoginMethod());
            $this->catchSystemException($ue);
        }
        catch (\Exception $e)
        {
            $this->catchSystemException($e);
        }
    }

    private function catchSystemException($e)
    {
        $obj = new \Spaark\Core\Output\StdOutput($this->request);
        $obj->exception($e);

        $this->obj = $obj;
        
        Output::attemptEnd();
    }

    public function shutdown()
    {
        $ttl = $this->obj->getRouteCacheTTL();
        
        if (!$this->cache || $ttl < 0)
        {
            $this->obj->shutdown();
            return;
        }

        $cache = new CacheEntry();
        $cache->setTTL($ttl);
        $cache->route = $this->route;

        Cache::route($cache);

        $this->obj->shutdown();
    }
}

?>