<?php

/**
 * The version of this release of Spaark.
 *
 * Set to "dev" for the versionless development code.
 */
define('VERSION', 'dev');

/**
 * Alias for PHP's DIRECTORY_SEPARATOR
 */
define('DS', DIRECTORY_SEPARATOR);

/**
 * The folder Spaark is running from
 *
 * This is the folder the initial file is located in
 */
define('ROOT', dirname($_SERVER['SCRIPT_FILENAME']) . DS);

/**
 * The path to the Spaark library
 */
define('SPAARK_PATH', dirname(__FILE__) . DS);

define('USE_APC', function_exists('apc_fetch'));

define('SIGNATURE', '');

?>