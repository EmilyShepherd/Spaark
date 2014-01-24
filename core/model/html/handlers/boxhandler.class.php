<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */

use \Spaark\Core\Model\HTML\Box;

/**
 * Handles tags in the box name space, and replaces them with
 * the appropriate opening and closing tags
 */
class BoxHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        $box = new Box($tag, $this->getAllAttrs($attrs));
        
        return
              $box->getTop()
            .   $content
            . $box->getBottom();
    }
}

?>