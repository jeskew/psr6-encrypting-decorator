<?php
namespace Jsq\CacheEncryption;

use Cache\Adapter\Common\CacheItem;
use Psr\Cache\CacheItemInterface;

abstract class ItemDecoratorTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @dataProvider ttlProvider
     *
     * @param $ttl
     */
    public function testProxiesExpiresAfterCallsToDecoratedItem($ttl)
    {
        $decorated = $this->getMock(CacheItemInterface::class);
        $decorated->expects($this->once())
            ->method('expiresAfter')
            ->with($ttl)
            ->willReturnSelf();

        $instance = $this->getInstance($decorated);
        $instance->expiresAfter($ttl);
    }

    public function ttlProvider()
    {
        return [
            [100],
            [new \DateInterval('P7D')]
        ];
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
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testHoldsPlaintextInMemory($data)
    {
        $decorated = $this->getMock(CacheItemInterface::class);
        $decorated->expects($this->any())
            ->method('set')
            ->willReturnSelf();
        $decorated->expects($this->never())
            ->method('get');

        $instance = $this->getInstance($decorated);
        $instance->set($data);
        $this->assertSame($data, $instance->get());
    }

    /**
     * @param CacheItemInterface $decorated
     * @return ItemDecorator
     */
    abstract protected function getInstance(CacheItemInterface $decorated);
}
