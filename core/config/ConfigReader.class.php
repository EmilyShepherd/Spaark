<?php namespace Spaark\Core\Config;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/// {{{ constants

/**
 * The setting that tells the ConfigReader to treat the entire file as
 * having config
 */
define('FILE_FULL', 0);

/**
 * The setting that tells the ConfigReader to allow data to be at the
 * bottom of the config file
 */
define('FILE_HEAD', 1);

/// }}}


        ////////////////////////////////////////////////////////


/// {{{ ConfigReader

/**
 * Reads config
 */
class ConfigReader extends \Spaark\Core\Cache\CacheEntry
{
    /**
     * If the file has data at the bottom of it, it will be stored here
     */
    protected $data   = NULL;
    
    /**
     * Parses config options out of the given raw data
     *
     * @param string $data The raw data to parse
     * @param int $mode FILE_FULL / FILE_HEAD
     * @param bool $replace If true, placeholders will be replaced
     */
    public function __construct($data, $mode = FILE_FULL, $replace = true)
    {
        $lineNum = 0;
        $data   = trim(str_replace
        (
            array('{ROOT}'),
            array(  ROOT  ),
            $data
        ));
        
        while (true)
        {
            $lineNum++;
            
            if (strlen($data) == 0)        break;
            
            $pos    = strpos($data, "\n");
            
            if (!$pos)
            {
                $line = trim($data);
                $data = '';
            }
            else
            {
                $line   = trim(substr($data, 0, $pos));
                $data   = trim(substr($data, $pos));
            }
            
            if ($lineNum >= 100)           exit;
            if (!$line || $line[0] == '#') continue;
            if ($mode == FILE_HEAD)
            {
                if ($line[0] != '@')
                {
                    $data = $line . ($data ? "\n" . $data : '');
                    break;
                }
                else
                {
                    $line = substr($line, 1);
                }
            }
            
            $pos    = strpos($line, ' ');
            if ($pos === false)
            {
                $item  = $line;
                $value = '';
            }
            else
            {
                $item   = substr($line, 0, $pos);
                $value  = ltrim(substr($line, $pos));
                
                if ($mode == FILE_HEAD)
                {
                    $item = strtolower($item);
                }
                else
                {
                    $item = strtoupper($item);
                }
            }
            
            $this->array[$item] = $value;
            
            if ($replace)
            {
                $data = str_replace
                (
                    '{'.$item.'}',
                    $value,
                    $data
                );
            }
        }
        
        $this->data = $data;
    }
    
    /**
     * Returns the data at the bottom of the config file.
     *
     * @return string The data of the config file
     * @throws InvalidMethodCallException If $mode = FILE_FULL
     */
    public function getData()
    {
        if ($this->data !== NULL)
        {
            return $this->data;
        }
        else
        {
            throw new InvalidMethodCallException
            (
                'ConfigReader::getData', '$mode = FILE_HEAD'
            );
        }
    }
}

// }}}

?>