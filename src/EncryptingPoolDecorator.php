<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class EncryptingPoolDecorator implements CacheItemPoolInterface
{
    /** @var CacheItemPoolInterface */
    private $decorated;
    /** @var array */
    private $memoized = [];

    public function __construct(CacheItemPoolInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function getItem($key)
    {
        if (empty($this->memoized[$key])) {
            $this->memoized[$key]
                = $this->decorate($this->decorated->getItem($key));
        }

        return $this->memoized[$key];
    }

    public function hasItem($key)
    {
        return $this->getItem($key)->isHit();
    }

    public function getItems(array $keys = [])
    {
        // Begin by fetching all items not yet memoized using the decorated
        // cache's getItems method -- there may be an optimization over getItem
        $toMemoize = $this->decorated
            ->getItems(array_keys(
                array_diff_key(array_flip($keys), $this->memoized)
            ));

        // Add all the fetched items to the memoized set, decorating each one
        // with an encryption-aware instance of CacheItemInterface
        array_walk($toMemoize, function ($leaf, $key) {
            $this->memoized[$key] = $this->decorate($leaf);
        });

        // Pull the sought items from the memoized set
        return array_intersect_key($this->memoized, array_flip($keys));
    }

    public function clear()
    {
        $this->memoized = [];
        return $this->decorated->clear();
    }

    public function deleteItems(array $keys)
    {
        foreach ($keys as $key) {
            unset($this->memoized[$key]);
        }

        return $this->decorated->deleteItems($keys);
    }

    public function deleteItem($key)
    {
        unset($this->memoized[$key]);

        return $this->decorated->deleteItem($key);
    }

    public function save(CacheItemInterface $item)
    {
        if (
            $item instanceof EncryptingItemDecorator
            && $this->validateEncryption($item)
        ) {
            $this->decorated->save($item->getDecorated());

            return $this;
        }

        throw new InvalidArgumentException('The provided cache item cannot'
            . ' be saved, as it did not originate from this cache.');
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        if (
            $item instanceof EncryptingItemDecorator
            && $this->validateEncryption($item)
        ) {
            $this->decorated->saveDeferred($item->getDecorated());

            return $this;
        }

        throw new InvalidArgumentException('The provided cache item cannot'
            . ' be saved, as it did not originate from this cache.');
    }

    public function commit()
    {
        return $this->decorated->commit();
    }

    abstract protected function decorate(CacheItemInterface $inner);

    abstract protected function validateEncryption(CacheItemInterface $item);
}
