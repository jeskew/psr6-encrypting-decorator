<?php
namespace Jsq\Cache\Password;

use Jsq\Cache\EncryptingItemDecoratorTest as BaseDecoratorTest;
use Psr\Cache\CacheItemInterface;

class EncryptingItemDecoratorTest extends BaseDecoratorTest
{
    protected function getInstance(CacheItemInterface $decorated)
    {
        return new EncryptingItemDecorator($decorated, 'abc', 'aes256');
    }
}
