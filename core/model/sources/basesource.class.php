<?php namespace Spaark\Core\Model\Sources;

use \Spaark\Core\Error\NoSuchMethodException;
use \Spaark\Core\Model\Model;


abstract class BaseSource extends Model implements iSource
{
// {{{ Variables

    /**
     * Array of criterea for selection / deletion / update
     *
     * In prefix notation
     */
    protected $where    = array( );

    /**
     * The maximum number of results to grab. If zero, no limit is used
     */
    protected $limit    = NULL;

    /**
     * Where to start grabbing results from. Defaults to 0
     */
    protected $start    = 0;

    /**
     * Array of order pairs, in the form (attribute, order). Where order
     * "ASC" / "DESC".
     */
    protected $order    = array( );

    /**
     * Array of items to attributes to group by
     */
    protected $group    = array( );

    /**
     * The result of the read
     */
    protected $res;

    /**
     * The class that called this - results will be boxed as this class
     */
    protected $class;

    /**
     * Array of models / tables / whatever to include
     */
    protected $model    = array( );

    /**
     * The current position (if we are looping through a dataset)
     */
    protected $position = 0;

    /**
     * If true, returned items won't be cached
     */
    protected $noCache  = false;

    /**
     * Cache of objects
     */
    protected $objs     = array( );

    /**
     * Does this source have a result
     */
    public function hasResult()
    {
        return $this->res || $this->objs;
    }

    // }}}

        ////////////////////////////////////////////////////////

// {{{ Abstract

    /**
     * This should get the given data row / bundle.
     *
     * @param int $pos The item to get
     * @return mixed   The raw data
     */
    abstract protected function _get($pos);

    /**
     * This should perform a read with the current options, setting
     * the value to $this->res
     */
    abstract protected function _read();

    /**
     * This should delete entries matching the current options
     */
    abstract protected function _delete();

    abstract protected function _count();

    abstract protected function _countRes();

    // }}}

        ////////////////////////////////////////////////////////

// {{{ Object

    /**
     * Builds the Source
     *
     * @param Model $class The class to build from
     */
    public function __construct($class)
    {
        $this->class = $class;
        
        $this->include($class::ModelName());
    }

    /**
     * Used to wrap method calls so they can all return $this for
     * chaining.
     *
     * Also allows CRUD operations to accept last minute where()
     * arguments:
     * <code>
     *   $source->where($where [, $value])->read();
     *   $source->read([$where [, $value]]);
     * </code>
     *
     * @param string $func The method name
     * @param array  $args The arguments
     * @throws NoSuchMethodException If that method doesn't exist
     */
    public function __call($func, $args)
    {
        switch ($func)
        {
            case 'create':
            case 'read':
            case 'update':
            case 'delete':
            case 'count':
                // Allow late passing of where() arguments
                if ($args)
                {
                    $this->where($args[0], \iget($args, 1));
                }

                return $this->{'_' . $func}();

            case 'where':
            case 'fwhere':
            case 'order':
            case 'limit':
            case 'group':
            case 'include':
            case 'massive':
                call_user_func_array(array($this, '_' . $func), $args);
                break;

            default:
                throw new NoSuchMethodException($this, $func);
        }

        return $this; // Chaining
    }

    // TODO: PHPDoc
    /**
     * Accepts a where array
     *
     * @param array $arg0
     */
    protected function _where($where, $value = NULL)
    {
        $this->where = $this->processWhere($where, $value);
    }

    /**
     * Special where() function called by the from() and findBy()
     * autobuilders
     *
     * Use this to translate camel case into whatever form you need
     * eg: fromUserId(6) -> where('user_id', 6)
     *
     * @param string $where The attribute name
     * @param mixed  $value The attribute value
     */
    public function fwhere($where, $value)
    {
        $this->where = $this->processWhere($where, $value);
    }

    /**
     * Accepts either an array of order pairs, or one order pair
     *
     * @param mixed $by Either a string representing an attribute, or an
     *     array of pairs
     * @param string $order If the first argument is a string attribute
     *     name, this should be "ASC" or "DESC"
     */
    protected function _order($by, $order = 'ASC')
    {
        if (is_array($by))
        {
            $this->order += func_get_args();
        }
        else
        {
            $this->order += array(array($by, $order));
        }
    }

    /**
     * Sets the limit and start offset for this data source
     *
     * @param int $limit The maximum number of entries to retrieve
     * @param int $start The starting offset
     */
    protected function _limit($limit, $start)
    {
        $this->limit = (int)$limit;
        $this->start = (int)$start;
    }

    /**
     * Accepts any number of attributes to group by
     *
     * _group([$attr1 [, $attr2 [, $attr3, ...]]])
     */
    protected function _group()
    {
        $this->group += func_get_args();
    }

    /**
     * Accepts any number of sources to include
     *
     * _include([$source1 [, $source2 [, $source3, ...]]])
     */
    protected function _include()
    {
        $this->model += func_get_args();
    }

    /**
     * If called, entries won't be cached when iterated over - instead
     * they will be thrown away.
     *
     * Useful if you know you are only going to use a large data set
     * once, and you don't want it hogging memory
     */
    protected function _massive()
    {
        $this->noCache = true;
    }

    // TODO: PHPDoc
    /**
     * Processes lazy where arguments into a proper where array
     *
     * @
     */
    protected function processWhere($where, $value = NULL)
    {
        if (is_array($where))
        {
            return $where;
        }
        else
        {
            if ($value)
            {
                return array('=', $where, $value);
            }
            else
            {
                return array('=', 'id', $where);
            }
        }
    }

    /**
     * Gets and boxes the given entry
     *
     * @param int $pos The entry to get
     * @return Model The boxed entry
     */
    public function get($pos)
    {
        if (!$this->hasResult()) $this->read();

        if (isset($this->objs[$pos]))
        {
            return $this->objs[$pos];
        }
        else
        {
            $class = $this->class;
            $obj   = $this->build($pos, $class);
            
            // 
            if (!$this->noCache || !is_object($obj))
            {
                $class::cache($obj, 'id', $obj->id);

                $this->objs[$pos] = $obj;
                
                // We are at the end of the resource. This means
                // everything will already have been cached (or we will
                // have explictly said we don't want it cached). Either
                // way, it's time to throw away the original resource.
                if (count($this->objs) == $this->count())
                {
                    $this->free();
                }
            }
            
            return $obj;
        }
    }

    /**
     * Boxes an entity from raw data if it doesn't already exist
     *
     * This version assumes the raw data is simply an array - this can
     * be overriden if this differs from source to source
     *
     * @param int $pos      The entry to get and box
     * @param string $class The class to box it as
     * @return Entity       The boxed entity
     */
    protected function build($pos, $class)
    {
        $row = $this->_get($pos);
        $row = current($row); // TODO: stop ignoring other shit
        $obj = Entity::getObj('id', $row['id']) ?: new $class();
        
        $obj->loadArray($row);

        return $obj;
    }

    /**
     * Frees the raw data result - called after a full interation
     *
     * The default behaviour is simply to unset the data result, sending
     * it to garbage collection. However if your object needs special
     * closing (eg, closing a port / IO handle) override this
     */
    protected function free()
    {
        $this->res = NULL;
    }

    /**
     * Counts the matching entries in the database, either by querying
     * or, if a read has already been performed, by counting the result
     * rows
     *
     * If you haven't done a read yet, but you know you are about to,
     * you can force it to load and count the result by specifing
     * $load = true. This can be useful:
     * <code>
     * 1:  if ($source->count($load))
     * 2:  {
     * 3:       foreach ($source as $item)
     * 4:           // Do something
     * 5:  }
     * </code>
     *
     * In this example, if $load = false, two queries will be performed:
     * at line 1: "SELECT COUNT(*) FROM table WHERE ..." and again at
     * line 3: "SELECT * FROM table WHERE ...". If $load where true, the
     * count at line one would force the selection of data, and both
     * lines 1 and 3 would share the same result.
     *
     * @param boolean $load Force loading of data?
     * @return int The number of matching entries
     */
    public function count($load = false)
    {
        if ($this->res instanceof BlackHole)
        {
            return 0;
        }
        elseif ($this->res)
        {
            return $this->_countRes();
        }
        elseif ($this->objs)
        {
            return count($this->objs);
        }
        elseif ($load)
        {
            $this->read();

            return $this->_countRes(); 
        }
        else
        {
            return $this->_count();
        }
    }

        ////////////////////////////////////////////////////////

// {{{ Iterator

    /**
     * Rewinds back to the first element of the Iterator
     *
     * @see http://www.php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        if (!$this->hasResult()) $this->read();

        $this->position = 0;
    }

    /**
     * This method is called after rewind() and next() to check if the
     * current position is valid
     *
     * @see http://www.php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        return $this->position < $this->count();
    }

    /**
     * Returns the key of the current element
     *
     * @see http://www.php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return $this->postion;
    }

    /**
     * Returns the current element
     *
     * @see http://www.php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        return $this->get($this->position);
    }

    /**
     * Moves the current position to the next element
     *
     * @see http://www.php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->position++;
    }

    // }}}

        ////////////////////////////////////////////////////////

// {{{ ArrayAccess

    /**
     * Called to check if an offset exists
     *
     * @see http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < $this->count();
    }

    /**
     * Gets the given offset
     *
     * Currently just calls get()
     *
     * @see http://www.php.net/manual/en/arrayaccess.offsetget.php
     * @see get($pos)
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Sets the value at the given offset
     *
     * Currently unsued
     *
     * @see http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        //
    }

    /**
     * Removes the entry at the given offset
     *
     * Currently unused
     *
     * @see http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        //
    }

    // }}}
}