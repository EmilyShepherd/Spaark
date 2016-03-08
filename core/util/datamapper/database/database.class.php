<?php namespace Spaark\Core\Util\DataMapper\Database;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Spaark\Core\Util\DataMapper\RelationalConverter;
use Spaark\Core\Model\Sources\Database\MySQLiConnection;
use Spaark\Core\Model\Collection\Set;

/**
 * Description of database
 *
 * @author Emily Shepherd
 */
class Database extends RelationalConverter
{
    protected $onlyDirty = true;
    
    protected $connection;
    
    public function __construct($info, $mask)
    {
        parent::__construct($info, $mask);
        
        $this->connection = MySQLiConnection::fromSingle();
        $this->connection->tableCache[$info['name']] = array(array( ));
        $this->data      = &$this->connection->tableCache[$info['name']][0];
        $this->subTables = &$this->connection->tableCache;
    }
    
    public function save($object)
    {
        parent::save($object);
        
        $this->_toArray();
    }
}
