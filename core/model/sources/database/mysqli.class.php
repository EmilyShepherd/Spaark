<?php namespace Spaark\Core\Model\Sources\Database;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */


/**
 * Represents a MySQLi Database connection
 */
class MySQLi extends Database
{
    const NOT_NULL_FLAG       = 1;
    const PRI_KEY_FLAG        = 2;
    const UNIQUE_KEY_FLAG     = 4;
    const BLOB_FLAG           = 16;
    const UNSIGNED_FLAG       = 32;
    const ZEROFILL_FLAG       = 64;
    const BINARY_FLAG         = 128;
    const ENUM_FLAG           = 256;
    const AUTO_INCREMENT_FLAG = 512;
    const TIMESTAMP_FLAG      = 1024;
    const SET_FLAG            = 2048;
    const NUM_FLAG            = 32768;
    const PART_KEY_FLAG       = 16384;
    const GROUP_FLAG          = 32768;
    const UNIQUE_FLAG         = 65536;

    const BAD_FIELD_ERROR     = 1054;
    const NO_SUCH_TABLE_ERROR = 1146;

    private $exceptions = array
    (
        self::BAD_FIELD_ERROR     => 'UnknownColumnException',
        self::NO_SUCH_TABLE_ERROR => 'NoSuchTableException'
    );

    /**
     * The persistent MySQLiConnection (keep the same one for the whole
     * script)
     */
    protected $mysqliClass = 'MySQLiConnection';

    const MYSQLI_HELPER = 'MySQLiConnection';

    private $cols;
    
    /**
     * Escapes the given value
     *
     * @param string $string The string to escape
     * @return string The escaped string
     */
    public function escape($string)
    {
        return '\'' . $this->mysqli->real_escape_string($string) . '\'';
    }
    
    /**
     * Executes the given SQL and returns the result
     *
     * @param string $sql The SQL to execute
     * @return MySQLiResult The result of the query
     * @throws SQLException If there was an error with the query
     */
    public function execute($sql)
    {
        $ret = $this->mysqli->query((string)$sql);
        
        if (is_object($ret))
        {
            $table      = NULL;
            $this->cols = array( );

            foreach ($ret->fetch_fields() as $i => $col)
            {
                if ($col->table != $table)
                {
                    $table = $col->table;
                    $this->cols[$table] = array( );
                }

                $this->cols[$table][] = $col->name;
            }
        }
        elseif (!$ret)
        {
            if (isset($this->exceptions[$this->mysqli->errno]))
            {
                $class =
                     'Spaark\Core\Model\Sources\Database\\'
                    . $this->exceptions[$this->mysqli->errno];

                throw new $class($this->mysqli->error);
            }
            else
            {
                throw new SQLException
                (
                    $this->mysqli->errno . ': ' . $this->mysqli->error,
                    $sql
                );
            }
        }
        
        return $ret;
    }
    
    /**
     * Returns the insert id of the last INSERT
     *
     * @return int The last insert ID
     */
    public function lastID()
    {
        return $this->mysqli->insert_id;
    }

    /**
     * Returns the number of rows in the result
     *
     * @return int The number of rows
     * @see $this->res
     */
    protected function _countRes()
    {
        return $this->res->num_rows;
    }

    /**
     * Gets the given row, returning it as "raw data" (an array)
     *
     * @param int $pos The row to get
     * @return array   The row data
     */
    public function _get($pos)
    {
        $this->res->data_seek($pos);
        
        $ret    = $this->res->fetch_array(\MYSQLI_NUM);
        $result = array( );
        $i      = 0;

        foreach ($this->cols as $table => $cols)
        {
            $result[$table] = array( );
            foreach ($cols as $name)
            {
                $result[$table][$name] = $ret[$i++];
            }
        }

        return $result;
    }

    /**
     * Frees the mysqli result
     */
    protected function free()
    {
        $this->res->free();
        $this->res = NULL;
    }
}