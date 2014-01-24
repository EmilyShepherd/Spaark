<?php namespace Spaark\Core\Model;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Represents a scalar value type that can be included in a form
 */
interface Variable
{
    /**
     * The regular expression that deems a string valid
     */
    const REGEX = '*';
    
    /**
     * The form input type this variable corresponds to
     *
     * HTML5 types are supported and will be implemented with javascript
     * for older browsers
     */
    const INPUT = 'text';
}