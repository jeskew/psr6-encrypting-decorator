<?php
namespace Jsq\CacheEncryption\Password;

use Jsq\CacheEncryption\ItemDecoratorTest as BaseDecoratorTest;
use Psr\Cache\CacheItemInterface;

class ItemDecoratorTest extends BaseDecoratorTest
{
    protected function getInstance(CacheItemInterface $decorated)
    {
        return new ItemDecorator($decorated, 'abc', 'aes256');
    }
}
