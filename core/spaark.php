<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * This is an alias for Instance::start()
 *
 * @param string $app The application file
 * @param string $uri The request URI
 * @see Instance:start()
 */
function Spaark($app = 'app', $uri = NULL)
{
    \Spaark\core\Instance::start($app, $uri);
}

?>