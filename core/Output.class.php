<?php namespace Spaark\Core;
/**
 * Spaark
 *
 * Copyright (C) 2012 Alexander Shepherd
 * Alexander.Shepherd@Gmail.com
 */
 
use \Spaark\Core\Config\Config;

define('OK', 200);
define('NOT_FOUND', 404);
define('ERROR', 500);
define('FOUND', 302);


/**
 * Manages Output
 *
 * Sets headers, caches output and compresses using gzip where supported
 */
class Output extends \Spaark\Core\Cache\CacheEntry
{
    // {{{ static
    
    /**
     * The Output object that Spaark is using
     */
    private static $obj;
    
    /**
     * If an output has been sent, this is true
     */
    private static $sent = false;
    
    private static $statuses = array
    (
        OK        => 'OK',
        NOT_FOUND => 'Not Found',
        ERROR     => 'Internal Server Error'
    );
    
    /**
     * Try to load the output buffer into the Output object
     *
     * @return bool True if the data was loaded
     */
    public static function fromOutput()
    {
        if (!self::$obj) return false;

        if (ob_get_level())
        {
            $contents = ob_get_contents();
            ob_clean();
        }
        else
        {
            $contents = '';
        }

        if (!$contents)
        {
            if (self::$obj->plain) return true;
            else                   return false;
        }

        self::$obj->loadData($contents);
        return true;
    }
    
    /**
     * Attempt to load from the output buffer and exit if it succeeds
     */
    public static function attemptEnd()
    {
        $contents = ob_get_contents();
        ob_clean();

        if (!$contents) return;

        self::$obj->loadData($contents);
        exit;
    }

    /**
     * Cleans the output buffer and creates a new Output object
     */
    public static function init()
    {
        ob_clean();
        self::$obj = new Output();
    }
    
    /**
     * Used to set values of the output object.
     *
     * E.g. to do $output->var = $val
     * You'd normally have to do: Output::getObj()->var = $val
     * This method allows for: Output::var($val)
     *
     * @param string $name The name of the variable
     * @param array $args The 0th entry is taken as the value
     */
    public static function __callStatic($name, $args)
    {
        self::$obj->$name = $args[0];
    }
    
    /**
     * Returns the Output object
     *
     * @return Output The output object
     */
    public static function getObj()
    {
        return self::$obj;
    }
    
    /**
     * Safely cleans the output buffer
     */
    public static function ob_clean()
    {
        if (ob_get_contents())
        {
            ob_clean();
        }
    }
    
    /// }}}
    
    
        ////////////////////////////////////////////////////////
    
    
    /// {{{ instance
    
    /**
     * The plain text version of this output (ie, the output)
     */
    protected $plain;
    
    /**
     * The gzip compressed version of this output
     */
    protected $gzip;
    
    /**
     * The calculated path to storage files for this output
     */
    private $path;
    
    /**
     * Sets the mime value of the array to text/html, which is Spaark's
     * default content type.
     */
    public function __construct()
    {
        $this->array['mime']   = 'text/html';
        $this->array['status'] = OK;
        
        parent::__construct();
    }
    
    /**
     * Sets the given data as this object's data
     */
    private function loadData($data)
    {
        $this->plain         = $data;
        
        if ($this->array['status'] == OK)
        {
            $this->array['etag'] = md5($data);
            $this->path          =
                  Config::CACHE_PATH()
                . $this->array['etag'] . '.output';
        }
    }

    /**
     * Saves the output and compressed output to the files then calls
     * CacheEntry::serialize() to save the array
     *
     * @return string The serialized Output object
     * @see CacheEntry::serialize()
     */
    public function serialize()
    {
        if ($this->path)
        {
            if ($this->plain)
            {
                file_put_contents($this->path, $this->plain);
            }
            if ($this->gzip)
            {
                file_put_contents($this->path . '.gz', $this->gzip);
            }
        }

        return parent::serialize();
    }
    
    /**
     * Creates the object from the serialized string by calling
     * CacheEntry::unserialize(), it then calculates the path for the
     * output cache from the saved etag.
     *
     * @param string $str The seriaized class
     */
    public function unserialize($str)
    {
        parent::unserialize($str);

        $this->path =
              Config::CACHE_PATH()
            . $this->array['etag'] . '.output';
    }
    
    /**
     * Returns the specified data type by attempting to load it from
     * its file.
     *
     * @param string $data The name of the data variable to get
     * @param string $ext The extension of its file
     * @return string The data
     */
    private function getData($data, $ext = '')
    {
        if
        (
            $this->$data === NULL           &&
            file_exists($this->path . $ext)
        )
        {
            $this->$data = file_get_contents($this->path . $ext);
        }

        return $this->$data;
    }
    
    /**
     * Send the output to the user, either via 304 response or by
     * sending the full output.
     *
     * @return bool True if content has been sent
     */
    public function send()
    {
        if (self::$sent || $this->send304() || $this->sendOutput())
        {
            return self::$sent = true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Checks to see if the client sent a If-None-Match request header,
     * and if it is equal to the etag entry in $this->array. If it is,
     * it will issue a 304 response, and return true.
     *
     * @return bool True if a 304 response was sent
     */
    private function send304()
    {
        if
        (
            $this->array['status'] == OK                           &&
            isset($this->array['etag'])                            &&
            !Config::DISABLE_304()                                 &&
            isset($_SERVER['HTTP_IF_NONE_MATCH'])                  &&
            $_SERVER['HTTP_IF_NONE_MATCH'] == $this->array['etag']
        )
        {
            //If the document hasn't changed, the links to resources
            //haven't changed. If caching is used properly, this would
            //imply the resources haven't changed, so the client
            //shouldn't need to request anything else.
            header('HTTP/1.1 304 Not Modified');
            header('Connection: close');

            $this->setCacheControl();

            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * Tries to find content to send back to the client. If the client
     * has said it supports gzip, the content will be sent in that
     * format.
     *
     * @return bool True if content was found and sent
     */
    private function sendOutput()
    {
        //Check if we can use gzip
        //If we can, try to find a cached version of it, otherwise
        //encode the plain data
        if
        (
            extension_loaded('zlib') &&
            isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
            strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false
        ) 
        {
            $content = $this->gzip =
                $this->getData('gzip', '.gz')
                  ?: gzencode($this->getData('plain'));
            
            header('Content-Encoding: gzip');
        }
        else
        {
            $content = $this->getData('plain');
        }

        if (!$content) return false;

        //Stop output buffering
        //Doesn't always work with one for some reason.
        self::ob_clean();
        while (ob_get_level()) ob_end_clean();

        header
        (
              'HTTP/1.1 '
            . $this->array['status'] . ' '
            . self::$statuses[$this->array['status']]
        );
        header('Content-Type: ' . $this->array['mime']);
        header('Content-Length: ' . strlen($content));
        header('X-Powered-By: spaark/' . VERSION . ' php/' . PHP_VERSION);
        
        if (isset($this->array['etag']))
        {
            header('Etag: '         . $this->array['etag']);
        }
        
        $this->setCacheControl();
        
        echo $content;
        flush();

        return true;
    }
    
    /**
     * Sets the Cache-Control response header.
     */
    private function setCacheControl()
    {
        if (isset($this->array['maxage']) && $this->array['maxage'])
        {
            header
            (
                'Cache-Control: '
                . 'max-age=' . $this->array['maxage']
            );
        }
    }
    
    /**
     * Checks the validity of this cache by calling CacheEntry::valid().
     * If it fails, it deletes its files.
     *
     * @return bool True if this cache is valid
     * @see CacheEntry::valid()
     */
    public function valid()
    {
        if
        (
            (!$this->plain && !file_exists($this->path)) ||
            (!parent::valid())
        )
        {
            if (file_exists($this->path))
            {
                unlink($this->path);
            }
            if (file_exists($this->path . '.gz'))
            {
                unlink($this->path . '.gz');
            }

            return false;
        }
        else
        {
            return true;
        }
    }
    
    // }}}
}

?>