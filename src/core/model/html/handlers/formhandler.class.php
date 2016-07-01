<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Model\XML\XMLParser;
use \Spaark\Core\Form;

/**
 * Adds the Spaark AJAX loader to a form's onsubmit attribute. Rewrites
 * Spaark inputs with HTML ones and adds the appropriate JavaScript to
 * support this.
 */
class FormHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * This is the number of the next form id. Formids are created from
     * the parent HTMLSegment's name with this number
     */
    private $i = 0;

    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        $action   = $this->getAttr($attrs, 'action',   true);
        $onsubmit = $this->getAttr($attrs, 'onsubmit', true);

        if (!strpos($action, '://'))
        {
            $xml = new XMLParser($content);
            $xml->setNS('\Spaark\Core\Model\HTML\Handlers');
            $xml->setHandler('input',    'InputHandler');
            $xml->setHandler('select',   'SelectHandler');
            $xml->setHandler('textarea', 'TextareaHandler');

            $form            = $this->handler->name . '_' . $this->i++;
            $xml->form       = $form;
            $xml->formObj    = Form::create($form, $action);
            $xml->validators = $this->handler->validators ?: array( );
            $xml->i          = 0;

            $content =
                  '<input '
                .     'type="hidden" '
                .     'name="_f" '
                .     'value="' . $form . '" '
                . '/>'
                . $xml->parse();

            $this->handler->validators = $xml->validators;

            return $this->build
            (
                'form',
                $attrs,
                array
                (
                    'action'   => $action,
                    'onsubmit' => $onsubmit //. 'f(event,this);'
                ),
                $content
            );
        }

        return true;
    }
}

