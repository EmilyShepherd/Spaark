<?php
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */


/**
 * Represents an Integer value
 */
class Integer extends Variable
{
    /**
     * When using this integer as a string, "0"s will be added to the
     * front of it to ensure it is this length
     *
     * Eg: with padding = 3, the integer 7 would be "007"
     */
    private $padding;

    /**
     * Sets the value of this Integer
     *
     * @param int $num The number
     * @throws SystemException If $num is not an integer
     */
    public function __construct($num)
    {
        if (!is_int($num) && !ctype_digit($num))
        {
            throw new SystemException($num . ' is not an int');
        }

        $this->value       = (int)$num;
        $this->stringValue = $num;
    }

    /**
     * Sets the padding for this Integer
     *
     * @param int The padding
     */
    public function setPadding($padding)
    {
        $this->padding = $padding;
    }

    /**
     * Returns this as a string, with padding "0"s if required
     *
     * @return string The integer, as a string
     */
    public function __toString()
    {
        $string = (string)$this->value;

        if ($this->padding && strlen($string) < $this->padding)
        {
            return
                  str_repeat('0', $this->padding - strlen($string))
                . $string;
        }

        return $string;
    }

    /**
     * Return a cloned version of this Integer but with its value
     * incremented by the given amount
     *
     * @param int $val How much to increment the new one by
     * @return Integer The new Integer
     */
    public function inc($val = 1)
    {
        $new         = clone $this;
        $new->value += $val;

        return $new;
    }
}

?>