<?php
namespace Jsq\Cache\Envelope;

use Jsq\Cache\EncryptingPoolDecoratorTest as BasePoolDecoratorTest;
use Jsq\Cache\PkiUtils;
use Psr\Cache\CacheItemPoolInterface;

class EncryptingPoolDecoratorTest extends BasePoolDecoratorTest
{
    use PkiUtils;

    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new EncryptingPoolDecorator(
            $decorated,
            self::getCertificate(),
            self::getKey()
        );
    }
}
