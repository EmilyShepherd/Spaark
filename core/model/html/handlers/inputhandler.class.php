<?php namespace Spaark\Core\Model\HTML\Handlers;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


use \Spaark\Core\Model\Model;


/**
 * Handles input tags by writing them as correct HTML, and generating
 * the appropriate JavaScript to validate them
 */
class InputHandler extends \Spaark\Core\Model\XML\ElementHandler
{
    /**
     * @see ElementHandler::parse()
     */
    public function parse($tag, $attrs, $content)
    {
        $name     = $this->getAttr($attrs, 'name',     false);
        
        if (!$name)
        {
            return true;
        }
        
        $type     = $this->getAttr($attrs, 'type',     true);
        $rule     = $this->getAttr($attrs, 'rule',     true);
        $required = $this->getAttr($attrs, 'required', true);
        $id       = $this->getAttr($attrs, 'id',       true);
        $value    = $this->getAttr($attrs, 'value',    true);
        $validate = false;
        $i        = $this->handler->i ?: 0;
        $type     = explode('.', $type);
        $from     = isset($type[1]) ? $type[1] : NULL;
        $type     = $type[0];
        
        $arr = array
        (
            'type'  => $type,
            'id'    => $id ?: $this->handler->form . '_' . $i,
            'value' => $value ?: '<?=\'\'?>'
        );
        
        $this->handler->i = $i + 1;
        
        
        
        if
        (
            ($fullType = Model::load($type)) ||
            ($fullType = Model::load('Vars\\' . $type))
        )
        {
            $validate             = $fullType;
            $arr['data-validate'] = $fullType;
            $arr['type']          = $fullType::INPUT_TYPE;
            
            $validators = $this->handler->validators;
            if (!isset($validators[$fullType]))
            {
                $validators[$fullType] = 'function(){}'; //Form::getValidationJs($type);
                $this->handler->validators = $validators;
            }
        }
        elseif ($required)
        {
            $arr['data-required'] = true;
        }
        
        $this->handler->formObj->addInput($name, $validate, $required, $from);
              
        return $this->build($tag, $attrs, $arr, false);
    }
}

?>