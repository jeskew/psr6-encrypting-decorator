<?php
namespace Jeskew\Cache;

use Jeskew\Cache\Fixtures\ArrayCacheItem;
use Jeskew\Cache\Fixtures\ArrayCachePool;
use Psr\Cache\CacheItemPoolInterface;

abstract class EncryptingPoolDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface
     */
    protected $decorated;

    /**
     * @var EncryptingPoolDecorator
     */
    protected $instance;

    public function setUp()
    {
        $this->decorated = $this->getMock(CacheItemPoolInterface::class);
        $this->instance = $this->getInstance($this->decorated);
    }

    public function testProxiesGetItemCallsToDecoratedCache()
    {
        $id = microtime();

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new ArrayCacheItem($id));

        $this->instance->getItem($id);
    }

    public function testProxiesDeleteItemCallsToDecoratedCache()
    {
        $id = microtime();

        $this->decorated->expects($this->once())
            ->method('deleteItem')
            ->with($id)
            ->willReturn(true);

        $this->assertTrue($this->instance->deleteItem($id));
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testEncryptsDataBeforePassingToDecoratedCache($data)
    {
        $id = microtime();

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new ArrayCacheItem($id));

        $this->decorated->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(function ($arg) use ($data) {
                    return $arg !== $data;
                })
            );

        $this->instance
            ->save($this->instance->getItem($id)->set($data));
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testDecryptsDataFetchedFromDecoratedCache($data)
    {
        $decorated = new ArrayCachePool;
        $instance = $this->getInstance($decorated);
        $id = microtime();

        $instance->save($instance->getItem($id)->set($data));

        $this->assertNotEquals($data, $decorated->getItem($id)->get());
        $this->assertEquals($data, $instance->getItem($id)->get());
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testReturnsNullWhenFetchRetrievesUnencryptedData($data)
    {
        $decorated = new ArrayCachePool;
        $instance = $this->getInstance($decorated);
        $id = microtime();

        $decorated->save($decorated->getItem($id)->set($data));

        $this->assertEquals($data, $decorated->getItem($id)->get());
        $this->assertNull($instance->getItem($id)->get());
    }

    public function testHasItemReturnsFalseWhenDecoratedCacheHasNoData()
    {
        $decorated = new ArrayCachePool;
        $instance = $this->getInstance($decorated);
        $id = microtime();

        $this->assertFalse($instance->hasItem($id));
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testHasItemReturnsFalseWhenKeyHasUnencryptedData($data)
    {
        $decorated = new ArrayCachePool;
        $instance = $this->getInstance($decorated);
        $id = microtime();

        $decorated->save($decorated->getItem($id)->set($data));
        $this->assertTrue($decorated->hasItem($id));
        $this->assertFalse($instance->hasItem($id));
    }

    public function cacheableDataProvider()
    {
        return [
            [1],
            ['string'],
            [['key' => 'value']],
            [['one', 2, 3.0]],
            [new \ArrayObject()],
            [[
                'one' => str_repeat('x', 1024*1024),
                'two' => str_repeat('y', 1024*1024),
                'three' => str_repeat('z', 1024*1024),
            ]],
        ];
    }

    /**
     * @param CacheItemPoolInterface $decorated
     * @return EncryptingPoolDecorator
     */
    abstract protected function getInstance(CacheItemPoolInterface $decorated);
}
