<?php namespace Spaark\Core\View;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Readys JavaScript for output. This includes:
 *   + Removing comments
 *   + Removing whitespace
 *   + Joining strings together
 */
class JavaScript extends OutputType
{
    /**
     * Takes an input script and compresses it for output
     *
     * @param string $script The JavaScript
     */
    public function __construct($script)
    {
        $this->output = $script;

        $this->removeComments('\/\*', '\*\/');
        $this->removeComments('(^|[^:])\/\/', '(\r|\n)');

        $this->output = URLParser::compressWhitespace
        (
            array
            (
                '{', '}', '(', ')', '[', ']', ';', ',',
                '+', '-', '*', '<', '>', '=', ':',
                '!=', '&&', '||'
            ),
            $this->output
        );

        $this->output = trim(str_replace
        (
            array('\'+\'', '"+"'),
            array(''    , ''),
            $this->output
        ));

        $this->addHeader('/*', '*/', ' ', '');
    }
}

