<?php
namespace Jsq\CacheEncryption;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class PoolDecorator implements CacheItemPoolInterface
{
    /** @var CacheItemPoolInterface */
    private $decorated;

    public function __construct(CacheItemPoolInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getItem($key)
    {
        return $this->decorate($this->decorated->getItem($key));
    }

    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    public function getItems(array $keys = [])
    {
        return array_map(function (CacheItemInterface $inner) {
            return $this->decorate($inner);
        }, $this->decorated->getItems($keys));
    }

    public function clear()
    {
        return $this->decorated->clear();
    }

    public function deleteItems(array $keys)
    {
        return $this->decorated->deleteItems($keys);
    }

    public function deleteItem($key)
    {
        return $this->decorated->deleteItem($key);
    }

    public function save(CacheItemInterface $item)
    {
        return $this->proxySave($item);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->proxySave($item, true);
    }

    private function proxySave(CacheItemInterface $item, $deferred = false)
    {
        if ($item instanceof ItemDecorator) {
            return $this->decorated
                ->{$deferred ? 'saveDeferred' : 'save'}($item->getDecorated());
        }

        throw new InvalidArgumentException('The provided cache item cannot'
            . ' be saved, as it did not originate from this cache.');
    }

    public function commit()
    {
        return $this->decorated->commit();
    }

    abstract protected function decorate(CacheItemInterface $inner);
}
