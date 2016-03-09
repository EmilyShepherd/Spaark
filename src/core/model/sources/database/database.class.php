<?php namespace Spaark\Core\Model\Sources\Database;

use \Spaark\Core\Model\Sources\BlackHole;

// {{{ exceptions

    /**
     * Thrown whenever an issue with SQL occurs
     *
     * For example:
     *   + Syntax errors
     *   + Unknown coloumns / tables
     *   + Illegal operations
     */
    class SQLException         extends   \Exception
    {
        public function __construct($error, $sql = '')
        {
            parent::__construct($error . ' ' . $sql);
        }
    }

    /**
     * Thrown when no results were found, when one or more were expected
     */
    class NoRowsException      extends SQLException {}

    /**
     * Thrown when more than one result was found, when one was expected
     */
    class TooManyRowsException extends SQLException {}

    /**
     * Thrown when you try to execute a query that will always fail.
     *
     * Example where array that would lead to this:
     * ['AND', ['=', id, array( )]]
     *
     * This may seem silly, but it can happen when chaining queries:
     *   $source1->read()
     *   $source2->read(array('AND', array('=', 'id', $source1)))
     */
    class AlwaysFalseSQLException extends SQLException
    {
        public function __construct()
        {
            parent::__construct('Refused to execute');
        }
    }

    class UnknownColumnException extends SQLException { }

    class NoSuchTableException extends SQLException { }

    // }}}

        ////////////////////////////////////////////////////////

abstract class Database extends \Spaark\Core\Model\Sources\BaseSource
{
// {{{ abstract

    /**
     * Escapes the given value
     *
     * @param string $string The string to escape
     * @return string The escaped string
     */
    abstract public function escape($string);

    /**
     * Should execute the sql, returning true for modifications or a raw
     * resource for SELECT, etc operation
     *
     * @param string $sql SQL to execute
     * @return mixed The result
     */
    abstract public function execute($sql);

    // }}}

        ////////////////////////////////////////////////////////

// {{{ object

    protected function _create($data)
    {
        $values = array( );
        $keys   = array( );

        foreach ($data as $key => $value)
        {
            $keys[]   =
                '`' . preg_replace('/[^A-Za-z0-9_]+/', '', $key) . '`';
            $values[] =
                $this->escape($value);
        }

        $this->execute
        (
              'INSERT INTO ' . $this->table
            .     '(' . implode(',', $keys) . ') '
            . 'VALUES '
            .     '(' . implode(',', $values) . ')'
        );

        return $this->lastID();
    }

    /**
     * Performs a read with the current options, setting the value to
     * $this->res
     */
    protected function _read()
    {
        try
        {
            $this->res = $this->executeWithConds
            (
                'SELECT * FROM ' . $this->table
            );

            return $this->_countRes() ? $this : NULL;
        }
        catch (AlwaysFalseSQLException $e)
        {
            $this->res = new BlackHole();

            return NULL;
        }
    }

    protected function _update($data)
    {
        try
        {
            $set = array( );

            foreach ($data as $key => $value)
            {
                $set[] = '`' . $key . '`=' . $this->escape($value);
            }

            $this->executeWithConds
            (
                  'UPDATE ' . $this->table . ' '
                . 'SET ' . implode(',', $set)
            );
        }
        catch (AlwaysFalseSQLException $e)
        {
            return 0;
        }
    }

    /**
     * Deletes entries matching the current options
     */
    protected function _delete()
    {
        try
        {
            $this->success = $this->executeWithConds
            (
                'DELETE FROM ' . $this->table
            );
        }
        catch (AlwaysFalseSQLException $e)
        {
            $this->success = true;
        }
    }

    /**
     * Queries the database to obtain a count of entries that match the
     * current criterea
     *
     * @return int The number of matches
     */
    protected function _count()
    {
        try
        {
            $this->res = $this->executeWithConds
            (
                'SELECT COUNT(*) as c FROM ' . $this->table
            );
            $ret       = $this->_get(0);
            $this->res = NULL;

            return $ret['']['c'];
        }
        catch (AlwaysFalseSQLException $e)
        {
            return 0;
        }
    }

    /**
     * Special where() function called by the from() and findBy()
     * autobuilders
     *
     * This translates camel case into underscores
     * eg: fromUserId(6) -> where('user_id', 6)
     *
     * @param string $where The attribute name
     * @param mixed  $value The attribute value
     */
    public function fwhere($where, $value)
    {
        $this->where = $this->processWhere
        (
            strtolower
            (
                preg_replace('/([a-z])([A-Z])/', '$1_$2', $where)
            ),
            $value
        );
    }

    /**
     * Creates the SQL from the options, then executes it
     *
     * @param string $string The starting SQL, as specified by one of
     *     _count() / _read() / _delete()
     * @return The raw resource
     */
    protected function _execute($start, $data = NULL)
    {
        $sql = $start . ' ' . implode(',', $this->model) . ' ';

        if ($data)
        {
            $sql .= 'SET ';

            foreach ($data as $key => $value)
            {

            }
        }

        return $this->execute($sql . $this->createSQLEnd());
    }

    protected function executeWithConds($sql)
    {
        return $this->execute($sql . $this->createSQLEnd());
    }

    /**
     * @getter
     */
    protected function table()
    {
        return implode(',', $this->model);
    }

    protected function createSQLEnd()
    {
        $where = $this->whereToSQL();

        if ($where == '0')
        {
            throw new AlwaysFalseSQLException();
        }

        $sql = ' WHERE ' . $where;

        if ($this->group)
        {
            $sql .= ' GROUP BY ' . implode(',', $this->group) . ' ';
        }
        if ($this->order)
        {
            $order = array( );
            foreach ($this->order as $part)
            {
                $order[] = $part[0] . ' ' . $part[1];
            }

            $sql .= ' ORDER BY ' . implode(',', $order) . ' ';
        }
        if ($this->limit)
        {
            $sql .= ' LIMIT ' . $this->start . ',' . $this->limit;
        }

        return $sql;
    }

    /**
     * Accepts any number of sources to include
     *
     * _include([$source1 [, $source2 [, $source3, ...]]])
     */
    protected function _include()
    {
        foreach (func_get_args() as $include)
        {
            $this->model[] = lcfirst($include);
        }
    }

    /**
     * Recursively converts a where array into SQL
     *
     * @param array $where If null, $this->where is used
     */
    protected function whereToSQL($where = NULL)
    {
        if ($where === NULL) $where = $this->where;
        if (empty($where))   return '1';

        switch (strtolower($where[0]))
        {
            case 'and':
            case 'or':
                $parts = array( );
                for ($i = 1; $i < count($where); $i++)
                {
                    $part = $this->whereToSQL($where[$i]);

                    // If this is worth adding, add it
                    if ($part != '0')
                    {
                        $parts[] = $part;
                    }
                    // This returned a false statement.
                    // If we are building an and statement, the whole
                    // lot is false
                    elseif (strtolower($where[0]) == 'and')
                    {
                        return '0';
                    }
                }

                return
                    '(' . implode(' ' . $where[0] . ' ', $parts) . ')';

            // Converts long cases to IN clauses:
            //   ["=", 'id', 1, 2, 3, 12] -> "id IN (1, 2, 3, 12)"
            case '=':
                if
                (
                    count($where) > 3   ||
                    is_array($where[2]) ||
                    $where[2] instanceof \Traversable
                )
                {
                    $parts  = array( );
                    $pieces = array_slice($where, 2);

                    foreach ($pieces as $item)
                    {
                        if
                        (
                            is_array($item) ||
                            $item instanceof \Traversable
                        )
                        {
                            foreach ($item as $subitem)
                            {
                                $parts[] = $this->escape($subitem);
                            }
                        }
                        else
                        {
                            $parts[] = $this->escape($item);
                        }
                    }

                    if (count($parts) == 0)
                    {
                        return '0';
                    }
                    elseif (count($parts) == 1)
                    {
                        return $where[1] . '=' . $parts[0];
                    }
                    else
                    {
                        return
                              $where[1]
                            . ' IN (' . implode(',', $parts) . ')';
                    }
                }
                // no break

            case '<=':
            case '<':
            case '>':
            case '>=':
            case '!=':
                 $where[2] = $where[2]{0} == '@'
                    ? substr($where[2], 1)
                    : $this->escape($where[2]);

                return $where[1] . $where[0] . $where[2];
        }
    }

    /**
     * Creates a query, escaping every other argument
     *
     * Eg:
     * <code>
     *   $email = "' OR 1 OR '";
     *   var_dump($db->createQuery
     *   (
     *       'SELECT * FROM table WHERE email=', $email, ' AND test=1'
     *   ));
     * </code>
     *
     * Outputs:
     *   SELECT * FROM table WHERE email='\' OR 1 \'' AND test=1
     *
     * This isn't used as part of the standard data source model.
     * However, for complex database tasks, sql is sometimes preferable.
     *
     * @param scalar [...] The SQL / values
     * @return string The constructed SQL
     */
    public function query()
    {
        $query = '';
        $var   = true;
        $args  = func_get_args();

        for ($i = 0; $i < count($args); $i++)
        {
            $var = !$var;
            if
            (
                $var &&
                (is_string($args[$i]) || is_object($args[$i]))
            )
            {
                $query .= $this->escape((string)$args[$i]);
            }
            else
            {
                $query .= $args[$i];
            }
        }

        return $this->execute($query);
    }
}