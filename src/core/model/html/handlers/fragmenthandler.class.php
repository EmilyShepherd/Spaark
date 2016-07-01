<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Config\ConfigReader;
use \Spaark\Core\Model\HTML\Fragment;

/**
 * Replaces the <fragment> tag with the appropriate HTML code by reading
 * the <fragment>'s child nodes / cdata and calulating varsIn for the
 * fragment object.
 *
 * The <fragment>'s content is parsed by the ConfigReader class.
 */
class FragmentHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        if (trim($content))
        {
            $conf = new ConfigReader($content);
            $conf = $conf->getArray();
        }
        else
        {
            $conf = array( );
        }

        $fragment = new Fragment($tag, $conf);
        return $fragment->getHTML();
    }
}

