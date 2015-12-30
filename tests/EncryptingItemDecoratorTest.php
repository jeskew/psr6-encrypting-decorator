<?php
namespace Jeskew\Cache;

use Psr\Cache\CacheItemInterface;

abstract class EncryptingItemDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testProxiesExpiresAtCallsToDecoratedItem()
    {
        $expiry = new \DateTimeImmutable('next Wednesday');

        $decorated = $this->getMock(CacheItemInterface::class);
        $decorated->expects($this->once())
            ->method('expiresAt')
            ->with($expiry)
            ->willReturnSelf();

        $instance = $this->getInstance($decorated);
        $instance->expiresAt($expiry);
    }

    public function testProxiesExpiresAfterCallsToDecoratedItem()
    {
        $ttl = new \DateInterval('P7D');

        $decorated = $this->getMock(CacheItemInterface::class);
        $decorated->expects($this->once())
            ->method('expiresAfter')
            ->with($ttl)
            ->willReturnSelf();

        $instance = $this->getInstance($decorated);
        $instance->expiresAfter($ttl);
    }

    abstract protected function getInstance(CacheItemInterface $decorated);
}
