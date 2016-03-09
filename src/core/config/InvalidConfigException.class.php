<?php namespace Spaark\Core\Config;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Thrown when there is an issue with a config file
 */
class InvalidConfigException extends SystemException
{
    /**
     * Sets the SystemException message with the given line number
     *
     * @param int $lineNum The line the issue occurred
     */
    public function __construct($lineNum)
    {
        parent::__construct
        (
              'There was a problem with the '
            . 'config data at line ' . $lineNum,
              'The site is misconfigured'
        );
    }
}

?>