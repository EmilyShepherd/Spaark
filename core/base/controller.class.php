<?php namespace Spaark\Core\Base;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Config\Config;

/**
 * Controllers have a route cache TTL and can be shut down.
 *
 * It's constructor automatically creates a page object, and this class'
 * view / model if they exist
 */
abstract class Controller extends Object
{
    const FROM = 'Controller';
    
    /**
     * The model performs application logic
     */
    protected $model;
    
    /**
     * The view is used to echo non-HTML outputs (eg, json / XML)
     */
    protected $view;
    
    /**
     * The Time To Live for this route.
     *
     * Requests that make it here will have the route cached so that it
     * doesn't need to go through the Router next time. The default is
     * 0 (indefinite cache)
     */
    protected $routeCacheTTL = -1;
    
    /**
     * Creates page / model / view objects for this class
     */
    /*public function __construct()
    {
        $class = get_class($this);
        $model = substr
        (
            $class,
            strlen(Config::NAME_SPACE()) + 9
        );
        $model = Config::NAME_SPACE() . 'Model' . $model;
        
        
        
        if (class_exists($model))
        {
            $this->model = new $model();
        }
        
        $view       = get_class($this) . 'View';
        if (class_exists($view))
        {
            $this->view = new $view($this->page);
        }
    }*/
    
    /**
     * Used by inheriting classes to indicate that it is only capable of
     * responding via AJAX
     *
     * @throws InvalidRequestException if this isn't an AJAX request
     */
    protected function ajax()
    {
        if (Vars::checkFlag(GET, 'ajax')) return;
        
        throw new InvalidRequestException('AJAX', 'application/json');
    }
    
    /**
     * Returns the route cache TTL
     *
     * @return int The route cache TTL
     */
    public function getRouteCacheTTL()
    {
        return $this->routeCacheTTL;
    }
    
    /**
     * Shuts down the Controller
     *
     * The shutdown function is called after Spaark's shutdown functions
     * have been run.
     *
     * This empty method is provided be default, if the Controller does
     * not need a shutdown action
     */
    public function shutdown() {}
}

?>