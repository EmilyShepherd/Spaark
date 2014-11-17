<?php namespace Spaark\Core\Model\Reflection;
/**
 *
 */

use \Spaark\Core\Model\Base;

class Reflector extends \Spaark\Core\Model\Base\Wrapper
{
    const WRAPPER_NAME = 'ReflectionClass';

    protected function __fromRef($cb)
    {
        $class        = static::WRAPPER_NAME;
        $this->object = new $class($cb[0], $cb[1]);
    }

    protected function _fromRef($ref)
    {
        if (static::isRefCorrect($ref)) return;

        if (is_array($ref))
        {
            static::checkCount($ref);

            return static::fromRef(array
            (
                                 static::getName($ref[0]),
                isset($ref[1]) ? static::getName($ref[1]) : ''
            ));
        }
        elseif (is_string($ref))
        {
            $ref2 = explode('::', ltrim($ref, '\\'));

            static::checkCount($ref2, $ref);
            return static::fromRef($ref2);
        }
        elseif (is_object($ref))
        {
            return static::fromRef(array(static::getName($ref)));
        }
        else
        {
            throw new Base\CannotCreateModelException
            (
                get_called_class(),
                'ref',
                $ref
            );
        }
    }

    private function getName($ref)
    {
        if (is_string($ref))
        {
            return ltrim($ref, '\\');
        }
        elseif (is_a($ref, '\Reflector') || is_a($ref, get_class()))
        {
            return ltrim($cb->getName(), '\\');
        }
        elseif (is_object($ref))
        {
            return ltrim(get_class($ref), '\\');
        }
    }

    private function isRefCorrect($ref)
    {
        if
        (
            is_array($ref)     && count($ref) == 2   &&
            is_string($ref[0]) && is_string($ref[1]) &&
            $ref[0]{0} != '\\'
        )
        {
            return true;
        }
    }

    private function checkCount($ref, $original = NULL)
    {
        if (count($ref) != 1 && count($ref) != 2)
        {
            throw new Base\CannotCreateModelException
            (
                get_called_class(),
                'ref',
                $original ?: $ref
            );
        }
    }
}