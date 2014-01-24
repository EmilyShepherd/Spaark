<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Puts script sources into the js response and removes the tag
 */
class ScriptHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * Puts script sources into the js response.
     *
     * @return string ''
     * @see XMLParser::addReturn()
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        if ($src = $this->getAttr($attrs, 'src', false))
        {
            $this->handler->addReturn
            (
                  'js',
                  $this->handler->script . ' '
                . str_replace(' ', '%20', $src)
            );
        }
        else
        {
            $this->handler->addReturn
            (
                'script',
                $this->handler->script . $content
            );
        }
        
        return '';
    }
}

?>