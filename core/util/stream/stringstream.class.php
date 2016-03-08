<?php namespace Spaark\Core\Util\Stream;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of stringstream
 *
 * @author Emily Shepherd
 */
class StringStream implements Stream
{
    private $string;
    
    private $pos = 0;
    
    public function __construct($string)
    {
        $this->string = $string;
    }
    
    public function next()
    {
        $value = $this->peek();
        $this->pos++;

        return $value;
    }

    public function peek()
    {
        if (isset($this->string{$this->pos}))
        {
            return $this->string{$this->pos};
        }
        else
        {
            return NULL;
        }
    }

    public function read($bytes)
    {
        $str        = substr($this->string, $this->pos, $bytes);
        $this->pos += $bytes;
        
        return $str;
    }

    public function seek($pos)
    {
        $this->pos = $pos;
    }
}
