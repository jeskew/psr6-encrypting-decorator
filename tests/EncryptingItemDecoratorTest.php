<?php
namespace Jsq\Cache;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemInterface;

abstract class EncryptingItemDecoratorTest extends \PHPUnit_Framework_TestCase
{
    use CacheDataProviderTrait;

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
        $foo = new CacheItem('foo');
        $this->getInstance($foo)->set('bar');
        $this->assertTrue($this->getInstance($foo)->isHit());
        $this->assertSame('bar', $this->getInstance($foo)->get());
        $this->assertNotSame('bar', $foo->get());

        $baz = new CacheItem('baz');
        $this->getInstance($baz)->set('quux');

        $klass = new \ReflectionClass(EncryptedValue::class);
        $prop = $klass->getProperty('cipherText');
        $prop->setAccessible(true);
        $prop->setValue($foo->get(), $baz->get()->getCipherText());
        $this->assertFalse($this->getInstance($foo)->isHit());
        $this->assertNull($this->getInstance($foo)->get());
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testEncryptsDataBeforeSavingInDecoratedCache($data)
    {
        $id = uniqid(time());
        $decorated = new CacheItem($id);
        $this->getInstance($decorated)->set($data);

        $this->assertNotEquals($data, $decorated->get());
        $this->assertEquals($data, $this->getInstance($decorated)->get());
    }

    /**
     * @param CacheItemInterface $decorated
     * @return EncryptingItemDecorator
     */
    abstract protected function getInstance(CacheItemInterface $decorated);
}
