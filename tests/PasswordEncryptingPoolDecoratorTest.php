<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemPoolInterface;

class PasswordEncryptingPoolDecoratorTest extends EncryptingPoolDecoratorTest
{
    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new PasswordEncryptingPoolDecorator($decorated, 'abc123');
    }
}
