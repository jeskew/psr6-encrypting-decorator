<?php
namespace Jeskew\Cache\Fixtures;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ArrayCachePool implements CacheItemPoolInterface
{
    /** @var array */
    private $cache = [];

    public function clear()
    {
        $this->cache = [];

        return true;
    }

    public function save(CacheItemInterface $item)
    {
        $this->cache[$item->getKey()] = $item->get();

        return $this;
    }

    public function hasItem($key)
    {
        return isset($this->cache[$key]);
    }

    public function getItem($key)
    {
        if (isset($this->cache[$key])) {
            return (new ArrayCacheItem($key, true))
                ->set($this->cache[$key]);
        }

        return new ArrayCacheItem($key);
    }

    public function getItems(array $keys = [])
    {
        return array_combine($keys, array_map([$this, 'getItem'], $keys));
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->save($item);
    }

    public function commit()
    {
        return true;
    }

    public function deleteItems(array $keys)
    {
        array_map([$this, 'deleteItem'], $keys);

        return true;
    }

    public function deleteItem($key)
    {
        unset($this->cache[$key]);

        return true;
    }
}
