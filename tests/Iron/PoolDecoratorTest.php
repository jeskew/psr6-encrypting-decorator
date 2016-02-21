<?php
namespace Jsq\CacheEncryption\Iron;

use Jsq\CacheEncryption\PoolDecoratorTest as BaseDecoratorTest;
use Jsq\Iron\Password;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecoratorTest extends BaseDecoratorTest
{
    use CacheDataProviderTrait;

    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new PoolDecorator($decorated, str_repeat('x', Password::MIN_LENGTH));
    }
}
