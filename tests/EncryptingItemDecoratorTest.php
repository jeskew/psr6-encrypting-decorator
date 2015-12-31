<?php
namespace Jeskew\Cache;

use Jeskew\Cache\Fixtures\ArrayCacheItem;
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

    public function testAuthenticatesCipherText()
    {
        $foo = new ArrayCacheItem('foo', true);
        $this->getInstance($foo)->set('bar');
        $this->assertTrue($this->getInstance($foo)->isHit());
        $this->assertSame('bar', $this->getInstance($foo)->get());
        $this->assertNotSame('bar', $foo->get());

        $baz = new ArrayCacheItem('baz', true);
        $this->getInstance($baz)->set('quux');

        $foo->set(['encrypted' => $baz->get()['encrypted']] + $foo->get());
        $this->assertFalse($this->getInstance($foo)->isHit());
        $this->assertNull($this->getInstance($foo)->get());
    }

    /**
     * @param CacheItemInterface $decorated
     * @return EncryptingItemDecorator
     */
    abstract protected function getInstance(CacheItemInterface $decorated);
}
