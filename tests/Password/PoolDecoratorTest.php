<?php
namespace Jsq\CacheEncryption\Password;

use Jsq\CacheEncryption\PoolDecoratorTest as BaseDecoratorTest;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecoratorTest extends BaseDecoratorTest
{
    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new PoolDecorator($decorated, 'abc123');
    }
}
