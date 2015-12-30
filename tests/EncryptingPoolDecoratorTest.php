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

    public function testProxiesClearCallsToDecoratedCache()
    {
        $this->decorated->expects($this->once())
            ->method('clear')
            ->with()
            ->willReturn(true);

        $this->assertTrue($this->instance->clear());
    }

    public function testProxiesCommitCallsToDecoratedCache()
    {
        $this->decorated->expects($this->once())
            ->method('commit')
            ->with()
            ->willReturn(true);

        $this->assertTrue($this->instance->commit());
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

    public function testProxiesGetItemsCallsToDecoratedCache()
    {
        $ids = [rand(0, 9), rand(10, 19), rand(20, 29)];

        $this->decorated->expects($this->once())
            ->method('getItems')
            ->with($ids)
            ->willReturn(array_map(function ($id) {
                return new ArrayCacheItem($id);
            }, $ids));

        $items = $this->instance->getItems($ids);

        $this->assertCount(count($ids), $items);
        $this->assertSame($ids, array_values(array_map(function ($item) {
            return $item->getKey();
        }, $items)));
    }

    public function testOnlyProxiesGetItemsCallsForItemsNotAlreadyMemoized()
    {
        $ids = [rand(0, 9), rand(10, 19), rand(20, 29)];

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($ids[0])
            ->willReturn(new ArrayCacheItem($ids[0]));

        $this->decorated->expects($this->once())
            ->method('getItems')
            ->with(array_values(array_intersect_key($ids, [1 => true, 2 => true])))
            ->willReturn(array_map(function ($id) {
                return new ArrayCacheItem($id);
            }, $ids));

        $this->instance->getItem($ids[0]);
        $items = $this->instance->getItems($ids);

        $this->assertCount(count($ids), $items);
        $this->assertSame($ids, array_values(array_map(function ($item) {
            return $item->getKey();
        }, $items)));
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

    public function testProxiesDeleteItemsCallsToDecoratedCache()
    {
        $ids = [rand(0, 9), rand(10, 19), rand(20, 29)];

        $this->decorated->expects($this->once())
            ->method('deleteItems')
            ->with($ids)
            ->willReturn(true);

        $this->assertTrue($this->instance->deleteItems($ids));
    }

    /**
     * @dataProvider savableDataProvider
     *
     * @param mixed $data
     * @param string $method
     */
    public function testEncryptsDataBeforeSavingInDecoratedCache($data, $method)
    {
        $id = microtime();

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new ArrayCacheItem($id));

        $this->decorated->expects($this->once())
            ->method($method)
            ->with(
                $this->callback(function ($arg) use ($data) {
                    return $arg !== $data;
                })
            );

        $this->instance
            ->{$method}($this->instance->getItem($id)->set($data));
    }

    public function savableDataProvider()
    {
        return array_merge(
            array_map(function (array $arr) {
                return array_merge($arr, ['save']);
            }, $this->cacheableDataProvider()),
            array_map(function (array $arr) {
                return array_merge($arr, ['saveDeferred']);
            }, $this->cacheableDataProvider())
        );
    }

    /**
     * @dataProvider savableDataProvider
     *
     * @param mixed $data
     * @param string $method
     *
     * @expectedException \Psr\Cache\InvalidArgumentException
     */
    public function testThrowsExceptionWhenUnsavableItemsProvidedToSave($data, $method)
    {
        $item = (new ArrayCacheItem('key'))->set($data);

        $this->instance->{$method}($item);
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

        $instance = $this->getInstance($decorated);

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
