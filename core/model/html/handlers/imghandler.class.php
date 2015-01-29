<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\View\URLParser;

/**
 * Rewrites the URL of images, to their cached version
 *
 * @see URLParser::parseURL()
 */
class ImgHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     * @see URLParser::parseURL()
     */
    public function parse($tag, $attrs, $content)
    {
        $src = $this->getAttr($attrs, 'src', true);
        
        if (!$src || strpos($src, '://'))
        {
            return true;
        }
        else
        {
            return $this->build
            (
                'img',
                $attrs,
                array
                (
                    'src' => URLParser::parseURL($src)
                ),
                false
            );
        }
    }
}

?>