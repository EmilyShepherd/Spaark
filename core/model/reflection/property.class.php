<?php namespace Spaark\Core\Model\Reflection;
/**
 *
 *
 */

/**
 * Reflects upon properies within a model, and parses their doc comments
 */
class Property extends Reflector
{
    /**
     * This class will be instanciated to $this->object
     */
    const WRAPPER_NAME = 'ReflectionProperty';

    /**
     * List of accepted parameters in a doc comment in the form:
     *   name => function_to_call
     */
    protected $acceptedParams = array
    (
        'localkey'   => 'mixed',
        'foreignkey' => 'mixed',
        'linktable'  => 'mixed',
        'readonly'   => 'bool',
        'save'       => 'bool',
        'id'         => 'setKey',
        'type'       => 'setType'
    );

    /**
     * Should this value be saved to a source?
     */
    protected $save;

    /**
     * Is this property read only?
     */
    protected $readonly;

    /**
     * What kind of property is this? Primary / Unique etc
     */
    protected $key;

    /**
     * 
     */
    protected $link;

    /**
     * This property's type
     */
    protected $type;

    /**
     * Will this property be an array of multiple items, or just one
     */
    protected $many = false;

    /**
     * 
     */
    protected $from;

    /**
     * If the property is an object, it will be loaded. These specify
     * the links in the data structures which link these together.
     *
     * By default, the localkey is the property name, with '_id'
     * appended and foreignkey is just 'id'
     */
    protected $localkey, $foreignkey;

    /**
     * When links represent a many to many relationship, an intermediary
     * table can be used
     */
    protected $linktable;

    /**
     * 
     */
    protected $direction;

    /**
     * Parses the doctype and infers the foreign and local keys if they
     * were not set explicitly
     *
     * @see Reflector::parse()
     */
    public function parse()
    {
        parent::parse();

        $this->object->setAccessible(true);

        if (!$this->foreignkey && !$this->localkey)
        {
            $this->localkey   = $this->object->getName() . '_id';
            $this->foreignkey = 'id';
        }
        elseif (!$this->localkey)
        {
            $parts          = explode('_', $this->foreignkey);
            $this->localkey = $parts[1];
        }
        elseif (!$this->foreignkey)
        {
            throw new \Exception('Unable to determin foreignkey');
        }
    }

    /**
     * Sets the key to be primary
     *
     * @param string $name Ignored
     * @param mixed $value Ignored
     */
    protected function setKey($name, $value)
    {
        $this->key = 'primary';
    }

    /**
     * Sets the type
     *
     * This should be a model name, either on it's own or in brackets
     * with "array". Eg:
     *   + ModelName
     *   + array(ModelName)
     *
     * @param string $name Ignored
     * @param string $value The value to set
     */
    protected function setType($name, $value)
    {
        if (!preg_match('/^array\((.*?)\)$/', $value, $match))
        {
            $this->type = $value;
        }
        else
        {
            $this->many = true;
            $this->type = $match[1];
        }
    }

    /**
     * @getter save
     */
    public function isProperty()
    {
        return (boolean)$this->type;
    }

    public function getValue($obj)
    {
        return $this->object->getValue($obj);
    }
}