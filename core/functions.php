<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 *
 * Functions
 *
 * This file contains global functions. This is for replacements /
 * extensions of PHP's functions.
 */


/**
 * Merges two arrays, using $array1's value if there is a conflict.
 *
 * This is similar to PHP's array_merge_recusrive except that conflicts
 * will not result in the structure from being changed.
 *
 * @param  array $tree1 The master tree (conflicts will side with this
 *                      tree)
 * @param  array $tree2 The tree to merge with
 * @return array        The merged tree
 */
function tree_merge_recursive(array $t1, array $t2, array $e = array( ))
{
    foreach ($t2 as $key => $value)
    {
        if (!isset($t1[$key]))
        {
            $t1[$key] = $value;
        }
        elseif (is_array($t1[$key]) && is_array($value))
        {
            $t1[$key] =
                tree_merge_recursive($t1[$key], $value);
        }
    }

    if (!$e)
    {
        return $t1;
    }
    else
    {
        $args    = array_slice(func_get_args(), 1);
        $args[0] = $t1;

        return call_user_func_array('tree_merge_recursive', $args);
    }
}

/**
 * Shortcut for isset($_GET[key])
 * 
 * Useful to make code look neater
 * 
 * @param mixed $key The array to check
 */
function get_isset($key)
{
    return isset($_GET[$key]);
}

/**
 * Shortcut for isset($_GET[key]) && is_array($_GET[key])
 * 
 * This is useful to make code look neater, and to help keep line length
 * short (especially in if / ternary statements)
 *
 * @param mixed $key The array to check
 */
function get_is_array($key)
{
    return isset($_GET[$key]) && is_array($_GET[$key]);
}

/**
 * Allows for method chaining with a new constructed object
 *
 * Eg:
 *   with(new Foo)->chain1()->chain2();
 *
 * @param  mixed $obj The object to return
 * @return mixed      The passed object
 */
function with($obj)
{
    return $obj;
}

/**
 * Safely gets the value at the given index from an array
 *
 * This checks if the index is set beforehand, if it's not, the default
 * value is returned. Useful for strict standards compliance without an
 * ugly ternary operator
 *
 * @param array  $array   The array to access
 * @param scalar $index   The index to obtain
 * @param mixed  $default The default value
 * @return mixed The value at the given index, or NULL if index is unset
 */
function iget($array, $index, $default = NULL)
{
    return isset($array[$index]) ? $array[$index] : $default;
}

/**
 * Used to test if a method exists and is public
 *
 * @param mixed $obj     An object / class name
 * @param string $method The method name
 * @return boolean       True if public method exists
 */
function is_public($obj, $method)
{
    return
        method_exists($obj, $method)     &&
        is_callable(array($obj, $method));
}

?>