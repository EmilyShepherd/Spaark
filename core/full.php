<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 *
 * @author Emily Shepherd
 * @pacakge Spaark
 * @subpackage core
 *
 * @compile-links
 */


/**
 * Bases
 *
 * Currently anything called by a router has to be a Controller, however
 * that is redundant if they require no model, so this may be changed.
 */
require_once FRAMEWORK . 'base/Controller.class.php';
require_once FRAMEWORK . 'base/Model.class.php';

/**
 * Routes requests
 *
 * Required to decide how to handle the request
 */
require_once FRAMEWORK . 'core/Router.class.php';

/**
 * Error handling
 *
 * Most of these errors cannot occur with static resources, so these
 * may be removed
 */
require_once FRAMEWORK . 'error/Error.class.php';
require_once FRAMEWORK . 'error/SystemException.class.php';
require_once FRAMEWORK . 'error/CaughtException.class.php';
require_once FRAMEWORK . 'error/InvalidMethodCallException.class.php';
require_once FRAMEWORK . 'error/NotFoundException.class.php';
require_once FRAMEWORK . 'error/NoSuchMethodException.class.php';
require_once FRAMEWORK . 'error/InvalidRequestException.class.php';

/**
 * Variable handling system
 *
 * Variable boxing isn't used by static pages, so these will be removed
 * from here and included by the ControllerRouter
 */
require_once FRAMEWORK . 'Vars/Vars.class.php';
require_once FRAMEWORK . 'Vars/AbstractVariable.class.php';
require_once FRAMEWORK . 'Vars/Variable.class.php';
require_once FRAMEWORK . 'Vars/InvalidInputException.class.php';
require_once FRAMEWORK . 'Vars/Collection.class.php';
require_once FRAMEWORK . 'ClassLoader.class.php';

/**
 * The templating system
 *
 * All of these except Page are redundant when the page has been cached,
 * so I will work at a way to remove these.
 */
require_once FRAMEWORK . 'View/OutputType.class.php';
require_once FRAMEWORK . 'View/URLParser.class.php';
require_once FRAMEWORK . 'View/Page.class.php';

/**
 * Used to handle form items
 */
require_once FRAMEWORK . 'Form.class.php';

/**
 * PHP replacement / extension functions
 */
require_once FRAMEWORK . 'functions.php';