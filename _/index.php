<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * This is Spaark
 *
 * If you have moved the Spaark files to somewhere else (e.g. a common
 * library), make sure to change this; Spaark cannot recover from a
 * failure to load Spaark!
 */
require './spaark/class/Spaark.php';


/**
 * Starts Spaark
 *
 * This will start Spaark with the default app ("app"). Spaark will
 * process the request and call the appropriate methods in your code.
 * 
 * To find the request URI, Spaark will inspect the
 * $_SERVER['SERVER_SOFTWARE'] variable and finds the uri from the
 * appropriate place (for example, Apache stores the value in
 * $_SERVER['REQUEST_URI']). If the server software is unknown, the
 * request will be taken from the $_SERVER['REQUEST_URI'] if it is set,
 * or $_SERVER['PHP_SELF'] . $_SERVER['QUERY_STRING'] otherwise.
 *
 * If this is not the correct URI, you will have to pass the correct one
 * to this function as the second parameter.
 * 
 * @param string $app The application file
 * @param string $uri The request URI
 */
Spaark();

?>