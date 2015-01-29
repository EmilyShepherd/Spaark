<?php namespace Spaark\Core\Model\Encoding;

/**
 * Represents a class that can do encoding
 */
abstract class Encoding extends \Spaark\Core\Model\Base\Entity
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
}