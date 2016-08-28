<?php namespace Spaark\Core\Model\Base\Entity;

class Group extends UnaryOperator
{
    protected $attribute;

    protected $builtTo = 0;

    public function __construct($input, $attr)
    {
        $this->attribute = $attr;
        parent::__construct($input);
    }

    public function get($pos)
    {
        $this->input->seek($this->builtTo);

        do
        {
            if (!($next = $this->input->current())) return NULL;

            $match  = $next[$this->attribute];
            unset($next[$this->attribute]);
            $return = array($next);
            $this->builtTo++;

            while ($next = $this->input->next())
            {
                if ($next[$this->attribute] === $match)
                {
                    unset($next[$this->attribute]);
                    $this->builtTo++;
                    $return[] = $next;
                }
                else
                {
                    break;
                }
            }

            $this->data[] = array
            (
                $this->attribute => array
                (
                    $this->attribute => $match,
                    'data'           => $return
                )
            );
        }
        while (!isset($this->data[$pos]));

        return $this->data[$pos];
    }

    public function calculateCost()
    {
        //
    }
} 
