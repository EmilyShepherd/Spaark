<?php namespace Spaark\Core\Model\Base\Entity;

class AttributeValue extends Predicate
{
    /**
     * Value
     *
     * @var mixed
     * @readable
     */
    protected $action = 'get';

    /**
     * Attribute
     *
     * @var Attribute
     * @readable
     */
    protected $attr;

    public function __construct($attr, $action = 'get')
    {
        $this->action = $action;
        $this->attr   = $attr;
    }

    public function match($line)
    {
        if (!isset($line[$this->attr])) return false;

        $val = $line[$this->attr];

        switch ($this->action)
        {
            case 'get':
                return is_array($val) ? $val[$this->attr] : $val;
            case 'count':
                return count($val);
        }
    }
}
