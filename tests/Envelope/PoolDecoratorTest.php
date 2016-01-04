<?php
namespace Jsq\CacheEncryption\Envelope;

use Jsq\CacheEncryption\PoolDecoratorTest as BasePoolDecoratorTest;
use Jsq\CacheEncryption\PkiUtils;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecoratorTest extends BasePoolDecoratorTest
{
    use PkiUtils;

    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new PoolDecorator(
            $decorated,
            self::getCertificate(),
            self::getKey()
        );
    }
}
