<?php
namespace Jsq\CacheEncryption\Iron;

use Iron\Iron;
use Iron\Password;
use Jsq\CacheEncryption\PoolDecoratorTest as BaseDecoratorTest;
use Psr\Cache\CacheItemPoolInterface;

class PoolDecoratorTest extends BaseDecoratorTest
{
    use CacheDataProviderTrait;

    public function setUp()
    {
        if (!class_exists(Iron::class)) {
            $this->markTestSkipped('The optional Iron-PHP dependency has not been installed');
        }
        
        parent::setUp();
    }

    protected function getInstance(CacheItemPoolInterface $decorated)
    {
        return new PoolDecorator($decorated, str_repeat('x', Password::MIN_LENGTH));
    }
}
