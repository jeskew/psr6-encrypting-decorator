<?php
namespace Jsq\Cache\Password;

use Jsq\Cache\EncryptingPoolDecoratorTest as BaseDecoratorTest;
use Psr\Cache\CacheItemPoolInterface;

class EncryptingPoolDecoratorTest extends BaseDecoratorTest
{
    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new EncryptingPoolDecorator($decorated, 'abc123');
    }
}
