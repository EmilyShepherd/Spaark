<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */
 

/**
 * Handles html5 tags
 *
 * @see HTMLSegment
 */
class HTML5Handler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        $divAttrs = array
        (
            'class' => $tag
        );
        if ($id = $this->getAttr($attrs, 'id', true))
        {
            $divAttrs['id'] = $id;
        }
        if ($this->getAttr($attrs, 'hidden', true))
        {
            $divAttrs['class'] .= ' hidden';
        }
        if ($class = $this->getAttr($attrs, 'class', true))
        {
            $divAttrs['class'] .= ' ' . $class;
        }

        return $this->build
        (
            $tag, $attrs, array( ),
            $this->build
            (
                'div', '', $divAttrs, $content
            )
        );

        return
              '<div class="' . $tag . ($hidden ? ' hidden' : '') . '">'
            .   $this->build($tag, $attrs, array( ), $content)
            . '</div>';
    }
}

?>