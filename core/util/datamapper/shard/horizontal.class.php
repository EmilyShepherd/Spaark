<?php namespace Spaark\Core\Util\DataMapper\Shard;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of horizontal
 *
 * @author Emily Shepherd
 */
class Horizontal extends \Spaark\Core\Util\DataMapper\Mapper
{
    private $data;
    
    public function save($data)
    {
        $this->data = $data;
        
        foreach ($this->info['parts'] as $test)
        {
            if ($this->_process($test['pattern']))
            {
                $source = $test['source']['type'];
                
                $source = new $source($test['source'], $this->mask, $this->links);
                
                $source->save($data);
            }
        }
    }
    
    private function _process($arg)
    {
        if ($arg === 'all')      return true;
        elseif (!is_array($arg)) return $arg;
        else switch ($arg[0])
        {
            case 'all':
                return true;
            case 'valueof':
                return $this->data->propertyValue($arg[1], false, false);
            case 'not':
                return !$this->_process($arg[1]);
              
            case 'and':
            case 'or':
            case '<':
            case '>':
            case '==':
            case '<=':
            case '>=':
            case '+':
            case '-':
            case '*':
            case '/':
                $side1 = addslashes($this->_process($arg[1]));
                $side2 = addslashes($this->_process($arg[2]));
                
                return eval("return '$side1' {$arg[0]} '$side2';");
        }
    }

}