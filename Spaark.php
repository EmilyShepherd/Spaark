<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 *
 * This file defines Spaark's environment variables and includes the
 * core files that Spaark requires to operate.
 *
 * Please note: Spaark's core is only capable of loading Output's from
 * the cache and sending them. Spaark will automatically load the rest
 * of itself if no cached Output exists.
 *
 * @author Alexander Shepherd
 * @package Spaark
 * @subpackage core
 *
 * @compile-links
 */


require_once dirname(__FILE__) . '/consts.php';

/**
 * Instance extends this because it is Static
 *
 * StaticClass just adds a constructor that throws an error
 */
require_once SPAARK_PATH . 'core/base/StaticClass.class.php';

/**
 * Required because CacheEntry extends ValueHolder
 */
require_once SPAARK_PATH . 'core/base/ValueHolder.class.php';

/**
 * Required to load cached Outputs
 */
//require_once SPAARK_PATH . 'core/cache/cache.class.php';

/**
 * Required because CacheEntry implements Cacheable
 */
require_once SPAARK_PATH . 'core/cache/cacheable.interface.php';

/**
 * Required because Output extends CacheEntry, and these exist within a
 * Cache
 */
require_once SPAARK_PATH . 'core/cache/cacheentry.class.php';

/**
 * Thrown when the requested cache doesn't exist
 */
require_once SPAARK_PATH . 'core/cache/cachemiss.class.php';

/**
 * Required to echo an Output
 */
require_once SPAARK_PATH . 'core/Output.class.php';

/**
 * Required to because Config extends this
 */
require_once SPAARK_PATH . 'core/config/ConfigReader.class.php';

/**
 * This class does the work
 *
 * Required to handle a request, either from cached output or by
 * building it
 */
require_once SPAARK_PATH . 'core/Request.class.php';

/**
 * PHP replacement / extension functions
 */
require_once SPAARK_PATH . 'core/functions.php';

/**
 * Contains the nice Spaark() function
 */
require_once SPAARK_PATH . 'core/spaark.php';


        ////////////////////////////////////////////////////////


/**
 * Required to load site config information, which contains the path to
 * the cache
 */
require_once SPAARK_PATH . 'core/error/nosuchmethodexception.class.php';
require_once SPAARK_PATH . 'core/base/object.class.php';
require_once SPAARK_PATH . 'core/model/model.class.php';
require_once SPAARK_PATH . 'core/model/entity.class.php';
require_once SPAARK_PATH . 'core/model/master.class.php';
require_once SPAARK_PATH . 'core/model/config.class.php';
require_once SPAARK_PATH . 'core/ClassLoader.class.php';

?>