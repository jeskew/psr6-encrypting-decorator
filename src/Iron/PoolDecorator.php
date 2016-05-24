<?php
namespace Jsq\CacheEncryption\Iron;

use Iron;
use Iron\PasswordInterface;
use Jsq\CacheEncryption\InvalidArgumentException;
use Jsq\CacheEncryption\PoolDecorator as BasePoolDecorator;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecorator extends BasePoolDecorator
{
    /** @var CacheItemPoolInterface */
    private $decorated;
    /** @var PasswordInterface */
    private $password;
    /** @var string */
    private $cipher;

    public function __construct(
        CacheItemPoolInterface $decorated,
        $password,
        $cipher = Iron\Iron::DEFAULT_ENCRYPTION_METHOD
    ) {
        if (!class_exists(Iron\Iron::class)) {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('You must install'
                . ' jsq/iron-php to use the Iron decorator.');
            // @codeCoverageIgnoreEnd
        }
        
        parent::__construct($decorated);
        $this->decorated = $decorated;
        $this->password = Iron\normalize_password($password);
        $this->cipher = $cipher;
    }

    protected function decorate(CacheItemInterface $inner)
    {
        return new ItemDecorator(
            $inner,
            $this->password,
            $this->cipher
        );
    }

    public function save(CacheItemInterface $item)
    {
        $this->finalizeItem($item);
        return $this->decorated->save($item->getDecorated());
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->finalizeItem($item);
        return $this->decorated->saveDeferred($item->getDecorated());
    }

    private function finalizeItem(CacheItemInterface $item)
    {
        if ($item instanceof ItemDecorator) {
            return $item->finalize();
        }
        
        throw new InvalidArgumentException('The provided cache item cannot'
            . ' be saved, as it did not originate from this cache.');
    }
}
