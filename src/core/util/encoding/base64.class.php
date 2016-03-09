<?php namespace Spaark\Core\Util\Encoding;

/**
 * Represents Base64 encoding
 */
class Base64 extends Encoding
{
    /**
     * @type int
     */
    protected $linelength;

    protected $buffer = '';

    /**
     * Decodes the given string
     *
     * If the input has been split into chunks, the line length will be
     * saved as $linelength.
     *
     * @param string $data The string to decode
     * @return string The decoded string
     */
    public function decode($data)
    {
        $lines = preg_split('/[\r\n]+/', $data);

        if (count($lines) > 1)
        {
            $this->linelength = strlen($lines[0]);
        }

        return base64_decode($data);
    }

    /**
     * Decodes the given string
     *
     * If a line length has been specified, the output will be split
     * into chunks.
     *
     * @param string $data The string to encode
     * @return string The encoded string
     */
    public function encode($data)
    {
        $data = base64_encode($data);

        if ($this->linelength)
        {
            $data = chunk_split($data, $this->linelength);
        }

        return $data;
    }

    public function nextChar()
    {

    }

    public function read($bytes)
    {
        if (strlen($this->buffer) < $bytes)
        {
            $sBytes        = static::dec2enc($bytes - strlen($this->buffer));
            $this->buffer .= base64_decode($this->stream->read($sBytes));
        }

        $ret           = substr($this->buffer, 0, $bytes);
        $this->buffer  = substr($this->buffer, $bytes);

        return $ret;
    }

    public function seek($pos)
    {
        $this->stream->seek(static::dec2enc($pos));
    }

    public static function dec2enc($bytes)
    {
        return 4 * ceil($bytes / 3);
    }

    public static function enc2dec($bytes)
    {
        return 3 * floor($bytes / 4);
    }
}