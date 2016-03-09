<?php namespace Spaark\Core\Util\Encoding;

/**
 * Represents a class that can do encoding
 */
abstract class Encoding implements \Spaark\Core\Util\Stream\Stream
{
    /**
     * Encodes the given data
     *
     * @param array $data Array of data to encode
     * @return string The encoded data
     */
    abstract public function encode($data);

    /**
     * Decodes the given data
     *
     * @param string $data The encoded data
     * @return array The decoded data
     */
    abstract public function decode($data);

    protected $stream;

    public function __construct(\Spaark\Core\Util\Stream\Stream $stream)
    {
        $this->steam = $stream;
    }

    public function close()
    {
        $this->stream->close();
    }
}