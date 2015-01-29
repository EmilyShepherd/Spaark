<?php namespace Spaark\Core\Model\Encoding;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


// {{{ exceptions

    /**
     * Thrown when a parsed JSON source has a syntax error in it
     */
    class InvalidJSONException extends \Exception {}

    // }}}

        ////////////////////////////////////////////////////////

/**
 * Parses JSON
 */
class JSON extends Encoding
{
    /**
     * The parsed json, as an array
     */
    private $data;
    
    private $i;
    
    private $line;
    
    private $char;
    
    private $clever;
    
    private $array;

    public function decode($data)
    {
        return json_decode($data);
    }

    public function encode($data)
    {
        return json_encode($data);
    }
    
    /**
     * Parses the gievn data and returns the array representing it
     *
     * @param string $data JSON string to parse
     * @param array The parsed data
     */
    public function parse($data, $clever = true)
    {
        $this->data   = $data;
        $this->i      = 0;
        $this->array  = array( );
        $this->clever = $clever;
        $this->line   = 1;
        $this->char   = 1;
        
        try
        {
            if ($this->nextNiceChar() == '{')
            {
                $return = $this->parse_bracket();
                
                if ($this->nextNiceChar(true) !== NULL)
                {
                    throw new InvalidJSONException
                    (
                        'Unexpected Extra Stuff'
                    );
                }
            }
            else
            {
                throw new InvalidJSONException('Expecting "{"');
            }
        }
        catch (\Exception $e)
        {var_dump($e->getMessage());var_dump($e->getLine());}
        
        return $return;
    }
    
    private function skipTo()
    {
        while(!in_array($c = $this->nextChar(), func_get_args()));
        
        return $c;
    }
    
    private function end()
    {
        return $this->i == strlen($this->data);
    }
    
    private function nextChar()
    {
        $char = $this->data{$this->i++};
        
        if
        (
            ($char == "\n") ||
            (
                ($char == "\r")                                 && 
                ($this->end() || $this->data{$this->i} != "\n")
            )
        )
        {
            $this->line++;
            $this->char = 1;
        }
        elseif (ctype_print($char))
        {
            $this->char++;
        }

        return $char;
    }
    
    private function nextNiceChar($endOk = false)
    {
        while (!$this->end())
        {
            $c = $this->nextChar();
            
            switch ($c)
            {
                case ' ':
                case "\t":
                case "\r":
                case "\n":
                    break;
                
                case '#':
                    $this->parse_linecomment();
                    break;
                    
                case '/':
                    $this->parse_comment();
                    break;
                
                default:
                    return $c;
            }
        }
        
        if (!$endOk)
        {
            throw new InvalidJSONException('Unexpected End of Input');
        }
        
        return NULL;
    }
    
    private function parse_linecomment()
    {
        $this->skipTo("\n", "\r");
    }
    
    private function parse_comment()
    {
        while (!$this->end())
        {
            switch($this->nextChar())
            {
                case '/':
                    $this->parse_linecomment();
                    return;
                    
                case '*':
                    do
                    {
                        $this->skipTo('*');
                    }
                    while ($this->nextChar() != '/');
                    return;
            }
        }
    }
    
    private function parse_bracket()
    {
        $arr = array( );
        
        while ($char = $this->nextNiceChar() OR true)
        {
            if ($char == '}') return $arr;
            
            //Be nice
            if ($char == ',') continue;
                
            if ($char == '"' || $char == '\'')
            {
                $label = $this->parse_string($char);
            }
            elseif (ctype_alpha($char))
            {
                $label = $char . $this->parse_alpha();
            }
            else
            {
                throw new InvalidJSONException
                (
                    'Unexpected "' . $char . '"'
                );
            }
            
            if ($this->nextNiceChar() != ':')
            {
                throw new InvalidJSONException
                (
                    'Unexpected "' . $char . '"'
                );
            }
            
            if ($this->clever)
            {
                $parts = explode('.', $label);
                
                $cleverArr = &$arr;
                
                foreach ($parts as $i => $part)
                {
                    if (!isset($cleverArr[$part]))
                    {
                        $cleverArr[$part] = array( );
                    }
                    
                    $cleverArr =& $cleverArr[$part];
                }
                
                $cleverArr = $this->parse_value();
            }
            else
            {
                $arr[$label] = $this->parse_value();
            }
            
            $char = $this->nextNiceChar();
            
            if ($char == '}')
            {
                return $arr;
            }
            elseif ($char == ',')
            {
                continue;
            }
            else
            {
                throw new InvalidJSONException
                (
                    'Unexpected "' . $char . '"'
                );
            }
        }
    }
    
    private function parse_array()
    {
        $arr = array( );
        
        while ($char = $this->nextNiceChar() OR true)
        {
            if ($char == ']') return $arr;
            
            $this->i--;
            $arr[] = $this->parse_value();
            
            $char = $this->nextNiceChar();
            
            if ($char == ']')
            {
                return $arr;
            }
            elseif ($char == ',')
            {
                continue;
            }
            else
            {
                throw new InvalidJSONException
                (
                    'Unexpected "' . $char . '"'
                );
            }
        }
    }
    
    private function parse_alpha($mask = array('.', '_'))
    {
        $alpha = '';
        
        while ($char = $this->nextChar() OR true)
        {
            if (in_array($char, $mask) || ctype_alnum($char))
            {
                $alpha .= $char;
            }
            else
            {
                $this->i--;
                return $alpha;
            }
        }
    }
    
    private function parse_string($type)
    {
        $string = '';
        
        while (!$this->end())
        {
            switch($c = $this->nextChar())
            {
                case $type:
                    return $string;
            
                case '\\':
                    $string .= $this->nextChar();
                    break;
                
                default:
                    $string .= $c;
            }
        }
    }
    
    private function parse_value()
    {
        $char = $this->nextNiceChar();

        if
        (
            in_array($char, array('.', '+', '-', '_')) ||
            ctype_alnum($char)
        )
        {
            $alpha = strtolower
            (
                  $char
                . $this->parse_alpha(array('.', '+', '-', '_'))
            );
            
            // true
            if ($alpha == 'true')
            {
                return true;
            }
            // false
            elseif ($alpha == 'false')
            {
                return false;
            }
            // null
            elseif ($alpha == 'null')
            {
                return NULL;
            }
            // (+|-) Decimal Float
            elseif (preg_match('/^(\+|\-)?[0-9]*\.[0-9]+$/', $alpha))
            {
                return floatval($alpha);
            }
            // (+|-)0b Binary Number
            elseif (preg_match('/^(\+|\-)?0b([0-9]+)$/', $alpha, $n))
            {
                return intval($n[1] . $n[2], 2);
            }
            // (+|-)0 Octal value
            elseif (preg_match('/^(\+|\-)?0[0-8]*$/', $alpha))
            {
                return intval($alpha, 8);
            }
            // (+|-) Decimal int
            elseif (preg_match('/^(\+|\-)?[0-9]+$/', $alpha))
            {
                return intval($alpha, 10);
            }
            // (+|-)0x Hex value
            elseif (preg_match('/^(\+|\-)?0x([a-f0-9]+)$/', $alpha, $n))
            {
                return intval($n[1] . $n[2], 16);
            }
            else
            {
                throw new InvalidJSONException
                (
                    'Unacceptable value "' . $alpha . '"'
                );
            }
        }
        elseif ($char == '\'' || $char == '"')
        {
            return $this->parse_string($char);
        }
        elseif ($char == '{')
        {
            return $this->parse_bracket();
        }
        elseif ($char == '[')
        {
            return $this->parse_array();
        }
        else
        {
            throw new InvalidJSONException
            (
                'Unexpected "' . $char . '"'
            );
        }
    }
    
    public function parseFile($file, $important = true)
    {
        if (file_exists($file . '.json'))
        {
            return $this->parse(file_get_contents($file . '.json'));
        }
        elseif (!$important)
        {
            return array( );
        }
        else
        {
            throw new \Exception($file);
        }
    }
}

// }}}