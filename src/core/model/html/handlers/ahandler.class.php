<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Handles link (a) tags
 *
 * @see HTMLSegment
 */
class AHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        $href     = $this->getAttr($attrs, 'href',    true);
        $onclick  = $this->getAttr($attrs, 'onclick', true);
        $target   = $this->getAttr($attrs, 'target',  false);
        $name     = $this->getAttr($attrs, 'name',    false);
        $id       = $this->getAttr($attrs, 'id',      true);
        $newAttrs = array( );

        if ($href)     $newAttrs['href']    = $href;
        if ($onclick)  $newAttrs['onclick'] = $onclick;

        if     ($id)   $newAttrs['id'] = $id;
        elseif ($name) $newAttrs['id'] = $name;

        if
        (
            $href                                &&
            !strpos($href, '://')                &&
            strpos($href, 'mailto:') === false   &&
            (
                !$target             ||
                $target != '_blank'
            )
        )
        {
            if ($target)
            {
                $newAttrs['onclick'] =
                      'Framework.loadContent'
                    . '('
                    .     '\'' . $href   . '\','
                    .     '\'' . $target . '\','
                    .     'event'
                    . ');';
            }
            else
            {
                $newAttrs['onclick'] = 'l(event,this);' . $onclick;
            }
        }

        return $this->build('a', $attrs, $newAttrs, $content);
    }
}

?>