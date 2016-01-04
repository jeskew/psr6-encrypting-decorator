<?php
namespace Jsq\Cache;

use Psr\Cache\CacheItemPoolInterface;

class EnvelopeEncryptingPoolDecoratorTest extends EncryptingPoolDecoratorTest
{
    use PkiUtils;

    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new EnvelopeEncryptingPoolDecorator(
            $decorated,
            self::getCertificate(),
            self::getKey()
        );
    }
}
