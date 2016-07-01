<?php namespace Spaark\Core\Cache;
/**
 * Spaark
 *
 * Copyright (C) 2012 Emily Shepherd
 * emily@emilyshepherd.me
 */

use \Spaark\Core\Base\ValueHolder;
use \Spaark\Core\Config\Config;

/**
 * Represents an entry in a Cache
 */
class CacheEntry extends ValueHolder implements Cacheable
{
    /**
     * True if this CacheEntry has been changed since being loaded
     */
    protected $dirty = true;

    public function __construct()
    {
        $this->array['expires'] = time() - 100;
    }

    /**
     * Sets the given index in the $this->array array with the given
     * value
     *
     * @param string $var The index in the array
     * @param mixed $val The value to set. Should be serializable.
     */
    public function __set($var, $val)
    {
        if ($var == 'ttl')
        {
            $this->setTTL($val);
        }
        else
        {
            $this->array[$var] = $val;
            $this->dirty       = true;
        }
    }

    /**
     * Sets the TTL for this CacheEntry. If less than or equal to zero
     * this will last forever. Otherwise an expires time will be
     * calculated and saved into $this->array
     *
     * @param int $ttl The TTL for this CacheEntry
     */
    public function setTTL($ttl)
    {
        if ($ttl == INDEFINITE)
        {
            unset($this->array['expires']);
            $this->dirty = true;
        }
        elseif ($ttl > 0)
        {
            $this->array['expires'] = time() + $ttl;
            $this->dirty            = true;
        }
        else
        {
            $this->array['expires'] = time() - 100;
        }
    }

    /**
     * Returns true if this CacheEntry is valid. This occurs when:
     *   + There is no expires time
     *   + The expires time has not occurred yet
     *
     * In the special case that Config::NO_CACHE() is true, this will
     * always return false.
     *
     * @return bool True if the CacheEntry is valid
     */
    public function valid()
    {
        return
        (
            !Config::NO_CACHE()                  &&
            (
                !isset($this->array['expires'])  ||
                $this->array['expires'] > time()
            )
        );
    }

    public function dirty()
    {
        return $this->dirty;
    }

    /**
     * Returns the serialized $this->array
     *
     * @return string The serialized $this->array
     */
    public function serialize()
    {
        return serialize($this->array);
    }

    /**
     * Populates $this->array from the given serialized array
     *
     * @param string $str The serialized array
     */
    public function unserialize($str)
    {
        $this->array = unserialize($str);
        $this->dirty = false;
    }
}

