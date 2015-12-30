<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;

class PasswordEncryptingItemDecoratorTest extends EncryptingItemDecoratorTest
{
    protected function getInstance(CacheItemInterface $decorated)
    {
        return new PasswordEncryptingItemDecorator($decorated, 'abc', 'aes256');
    }
}
