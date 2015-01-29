<?php namespace Spaark\Core\Model\Encoding;

/**
 * Represents Base64 encoding
 */
class Base64 extends Encoding
{
    /**
     * @type int
     */
    protected $linelength;
    
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
}