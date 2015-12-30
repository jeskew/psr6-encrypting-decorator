<?php
namespace Jeskew\Cache\Fixtures;

use Psr\Cache\CacheItemInterface;

class ArrayCacheItem implements CacheItemInterface
{
    /** @var mixed */
    private $value;
    /** @var string */
    private $key;
    /** @var bool */
    private $isHit;
    /** @var \DateTimeInterface */
    private $expiration;

    public function __construct($key, $isHit = false)
    {
        $this->key = $key;
        $this->isHit = $isHit;
    }

    public function get()
    {
        return $this->value;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    public function isHit()
    {
        return $this->isHit;
    }

    public function expiresAt($expiration)
    {
        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter($time)
    {
        $this->expiration = \DateTime::createFromFormat('U', time() + $time);

        return $this;
    }
}
