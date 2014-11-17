<?php namespace Spaark\Core\Model\Reflection;
/**
 *
 *
 */

class Property extends Reflector
{
    /**
     * @readonly
     */
    protected $save;

    /**
     * @readonly
     */
    protected $readonly;

    /**
     * @readonly
     */
    protected $key;

    /**
     * @readonly
     */
    protected $link;

    protected $type;
    protected $many = false;
    protected $from;
    protected $localKey;
    protected $foreignKey;
    protected $linkTable;
    protected $direction;

    const WRAPPER_NAME = 'ReflectionProperty';

    public function parse()
    {
        preg_match_all
        (
              '/^[ \t]*\*[ \t]*@([a-zA-Z]*)[ \t]*'
            . '('
            .     '((array)\(([a-zA-Z0-9]+)(\.([a-zA-Z0-9]+))?\))' . '|'
            .     '(' .     '([a-zA-Z0-9]+)(\.([a-zA-Z0-9]+))?)'
            . ')'
            . '\s*$/m',
            $this->object->getDocComment(),
            $matches
        );

        var_dump($matches);

        foreach ($matches[0] as $i => $value)
        {
            $name = $matches[1][$i];
            $data = trim($matches[2][$i]);

            switch ($name)
            {
                case 'localKey':
                case 'foreignKey':
                case 'linkTable':
                case 'key':
                    $this->$name = $data;
                    break;

                case 'readonly':
                    $this->readonly = true;
                    break;

                case 'save':
                    if (!$data || strtolower($data) == 'true')
                    {
                        $this->save = true;
                    }
                    elseif (strtolower($data) == 'false')
                    {
                        $this->save = false;
                    }
                    else
                    {
                        //
                    }
                    break;

                case 'id':
                    $this->key = 'primary';
                    break;

                case 'type':
                    if ($matches[4][$i] == 'array')
                    {
                        $this->many = true;
                    }

                    $this->type = $matches[5][$i];
                    break; 
            }
        }

        if (!$this->foreignKey && !$this->localKey)
        {
            $this->localKey   = $this->object->getName() . '_{id}';
            $this->foreignKey = '{id}';
        }
        elseif (!$this->localKey)
        {
            $parts          = explode('_', $this->foreignKey);
            $this->localKey = $parts[1];
        }
        elseif (!$this->foreignKey)
        {
            throw new \Exception('Unable to determin foreignKey');
        }
    }
}