<?php namespace Spaark\Core\Output;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Instance;
use \Spaark\Core\Config\Config;
use \Spaark\Core\Output;
use \Spaark\Core\Model\Reflection\Controller;
use \Spaark\Core\Model\Base\CannotCreateModelException;
use \Spaark\Core\Cache\Cache;
use \Spaark\Core\Cache\CacheMiss;
use \Spaark\Core\Cache\CacheEntry;
use \Spaark\Core\ClassLoader;


/**
 * Routes the Controllers
 */
class Router extends \Spaark\Core\Base\Controller
{
    /**
     * The object that this router is attempting to use. This will be
     * assumed to be the successful one in the shutdown phase
     */
    private $obj;

    /**
     * The exploded parts of the request
     */
    private $parts;

    /**
     * The path the router is currently inspecting
     */
    private $path;

    private $triedPath;

    private $routeTree = array( );

    private $routeNode;

    /**
     * The array of arguments the router is attempting to use
     */
    private $args = array( );

    private $class;

    private $method;

    /**
     * This is the pointer in $this->parts that we are currently looking
     * at
     */
    private $i;

    /**
     * Trys to load the controller, assuming the request has no
     * arguments
     *
     * / maps to:
     *   /Index::home()
     *
     * /some maps to:
     *   /some/some::home()
     *   /some::home()
     *   /Index::some()
     *
     * /some/sample/path maps to:
     *   /some/sample/path/path::home()
     *   /some/sample/path::home()
     *   /some/sample/sample::path()
     *   /some/sample::path()
     */
    public function method()
    {
        $req   = trim(Instance::getRequest(), '/');

        //Takes care of "/"
        if (!$req)
        {
            $this->tryDefault();
            return;
        }

        $this->path = $req;
        $parts      = explode('/', $req);

        //For /some/sample/path, this trys
        //  /some/sample/path/path:home()
        //  /some/sample/path::home()
        $this->includeAndTryClass();

        if (count($parts) == 1)
        {
            if ($parts[0] != 'home')
            {
                //For /path, this trys
                //  /Index::path()
                $this->tryDefault($parts[0]);
            }
        }
        else
        {
            //For /some/sample/path, this trys
            //  /some/sample/sample::path()
            //  /some/sample::path()
            $this->includeAndTryClass();
        }
    }

    /**
     * Trys to run Index::$method() with no arguments
     *
     * @param string $method The method name to try
     */
    private function tryDefault($method = 'home')
    {
        $this->full_class =
              $this->config->app['namespace']
            . 'Controller\Index';

        if (class_exists($this->full_class))
        {
            $this->tryRoute($method);
        }
    }

    /**
     * Trys to load the controller, assuming part of the request is
     * arguments
     *
     * /some/sample/path maps to
     *   /some/some::home('path')
     *   /some::home('path')
     *   /Index::some('path')
     *   /Index::home('some', 'path')
     */
    public function args()
    {
        //This is an empty request, it cannot have arguments
        if (Instance::getRequest() == '/') return;

        $req        = trim(Instance::getRequest(), '/');
        $this->path = $req;

        $i = 0;
        //Now that we have found the last existing directory, we can
        //bubble up again running tests
        while ($this->path != '.')
        {
            $this->includeAndTryClass();
        }

        $this->full_class = $this->config->app['namespace'] . 'Controller\Index';

        //It failed the other tests, try to call the method in the
        //Default class / or the Default home
        if (class_exists($this->full_class))
        {
            if ($this->method != 'home')
            {
                $this->tryRoute($this->method, $this->args);
            }

            if ($this->method)
            {
                array_unshift($this->args, $this->method);
            }

            $this->tryRoute('home', $this->args);
        }
    }

    /**
     * This will try to include the class file before attempting to run
     * it.
     *
     * If the path is /some/path, this will try to include:
     *   /some/path/path.class.php
     *   /some/path.class.php
     *
     * @see ControllerRouter::tryClass()
     */
    private function includeAndTryClass()
    {
        $this->full_class =
              $this->config->app['namespace'] . 'Controller\\'
            . strtolower(str_replace('/', '\\', $this->path));
        $this->class      =
            strtolower(pathinfo($this->path, PATHINFO_BASENAME));

        if (ctype_alnum(str_replace('\\', '', $this->full_class)))
        {
            if (ClassLoader::load($this->full_class . '\\' . $this->class, false))
            {
                $this->full_class .= '\\' . $this->class;

                $this->tryClass();
            }

            if (ClassLoader::load($this->full_class, false))
            {
                $this->tryClass();
            }
        }

        $this->goUp();
    }

    /**
     * Try to run the method / home from the class
     */
    private function tryClass()
    {
        //Try className::home(args)
        if (!$this->method)
        {
            $this->tryRoute('home', $this->args);
        }
        else
        {
            // Duplicate content is bad, therefore we won't respond to calls
            // to "home" as these can be served without it.
            // Eg: /MyClass/     -> MyClass::home()
            // Eg: /MyClass/home -> Shouldn't also map to MyClass::home()
            // (A failed route with home in it will attempt a soft redirect
            // before giving a 404 error)
            if ($this->method != 'home')
            {
                //Try ClassName::methodName(args)
                $this->tryRoute($this->method, $this->args);
            }

            //ClassName::methodName(args) doesn't exist, so push
            //"methodName" into the arguments array
            array_unshift($this->args, $this->method);
            $this->method = NULL;

            //Try ClassName::home(methodName, args)
            $this->tryRoute('home', $this->args);
        }
    }

    /**
     * Goes up
     *
     * Sets $this->path to be the parent directory of $this->path and
     * decrements $this->i
     */
    private function goUp()
    {
        $this->path   = dirname($this->path);

        if ($this->method)
        {
            array_unshift($this->args, $this->method);
        }

        $this->method = $this->class;
    }

    private function tryRoute($method, $args = array( ))
    {
        //$class = Controller::from($this->full_class);

        if (!ctype_alnum($method)) return;

        $parts           = explode('/', trim(Instance::getRequest(), '/'));
        //$this->routeTree = array( );
        $this->routeNode =& $this->routeTree;

        for ($i = 0; $i < count($parts) - count($args); $i++)
        {
            if (!$this->routeNode)
            {
                $this->routeNode = array( );
            }
            if (!isset($this->routeNode[$parts[$i]]))
            {
                $this->routeNode[$parts[$i]] = array( );
            }
            $this->routeNode =& $this->routeNode[$parts[$i]];
        }

        $args = array_values($args);

        $this->tryMethod
        (
            strtolower($_SERVER['REQUEST_METHOD']) . '_' . $method,
            $args,
            $_SERVER['REQUEST_METHOD']
        );
        $this->tryMethod($method, $args, '*');
    }

    /**
     * Attempt to run a certain route
     *
     * If output was echo'd, this will call exit (to cause Spaark to go
     * into its shutdown phase). Otherwise it will allow the router to
     * carry on searching.
     *
     * @param string $className The name of the class to try
     * @param string $methodName The name of the method to try
     * @param array $args The arguments to give to the method
     */
    private function tryMethod($methodName, $args, $reqMethod)
    {
        $className   = $this->full_class;
        $class       = Controller::fromController($className);
        $count       = count($args);

        foreach ($class->getMethod($methodName, $count) as $method)
        {
            if ($method->isPublic())
            {
                $params  = $method->getParameters();
                $newArgs = array( );
                $j       = -1;

                foreach ($params as $i => $param)
                {
                    if (!isset($args[++$j])) break;

                    $arg         = $args[$j];
                    $cast        = $param->cast;
                    $newArgs[$i] = urldecode($arg);

                    if ($cast)
                    {
                        try
                        {
                            if ($param->args > 1)
                            {
                                $newArgs[$i] = array( );

                                for ($k = $j; $k < $j + $param->args; $k++)
                                {
                                    $newArgs[$i][] = urldecode($args[$k]);
                                }

                                $j           = $k - 1;
                                $newArgs[$i] = new $cast
                                (
                                    $newArgs[$i]
                                );
                            }
                            else if ($param->from)
                            {
                                if ($param->fromArg)
                                {
                                    $innerCast = $param->fromArg;
                                    $newArgs[$i] = new $innerCast
                                    (
                                        $newArgs[$i]
                                    );
                                }

                                $fromMethod = 'from' . ucfirst($param->from);

                                $newArgs[$i] = $cast::$fromMethod($newArgs[$i]);
                            }
                            else
                            {
                                $newArgs[$i] = new $cast
                                (
                                    $newArgs[$i]
                                );
                            }
                        }
                        catch (CannotCreateModelException $ccme)
                        {
                            if ($param->optional)
                            {
                                $newArgs[$i] = NULL;
                                continue;
                            }
                            else
                            {
                                continue 2;
                            }
                        }
                    }
                }

                try
                {
                    $this->obj = new $className();
                    echo $method->invokeArgs($this->obj, $newArgs);
                    Output::attemptEnd();
                }
                catch (NotFoundException $nfe){}
            }
        }
    }

    public function getRouteCacheTTL()
    {
        if ($this->routeNode)
        {
            return -1;
        }
        else
        {
            return 0;
        }
    }

    public function shutdown()
    {
        try
        {
            $routes = Cache::load('routes', 'global');

            $routes->routes = tree_merge_recursive
            (
                $routes->routes,
                $this->routeTree
            );
        }
        catch (CacheMiss $cm)
        {
            $routes = new CacheEntry();
            $routes->setTTL(INDEFINITE);
            $routes->routes = $this->routeTree;
        }

        Cache::save('routes', $routes, 'global');
    }
}

?>