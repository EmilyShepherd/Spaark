<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use Spaark\Core\Instance;

/**
 * This is an alias for Instance::start()
 *
 * @param string $app The application file
 * @param string $uri The request URI
 * @see Instance:start()
 */
function Spaark($app = 'app', $uri = NULL)
{
    Instance::bootstrap($app, $uri);
}

