<?php
namespace Jsq\Cache;

use Cache\Adapter\Common\CacheItem;
use Cache\Adapter\Doctrine\DoctrineCachePool;
use Cache\IntegrationTests\CachePoolTest;
use Doctrine\Common\Cache\ArrayCache;
use Psr\Cache\CacheItemPoolInterface;

abstract class EncryptingPoolDecoratorTest extends CachePoolTest
{
    use CacheDataProviderTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|CacheItemPoolInterface
     */
    protected $decorated;

    /**
     * @var EncryptingPoolDecorator
     */
    protected $instance;

    /** @var CacheItemPoolInterface */
    private static $persistentCache;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$persistentCache = new DoctrineCachePool(new ArrayCache);
    }

    public function setUp()
    {
        parent::setUp();
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
        $id = uniqid(time());

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new CacheItem($id));

        $this->instance->getItem($id);
    }

    public function testProxiesGetItemsCallsToDecoratedCache()
    {
        $ids = [rand(0, 9), rand(10, 19), rand(20, 29)];

        $this->decorated->expects($this->once())
            ->method('getItems')
            ->with($ids)
            ->willReturn(array_map(function ($id) {
                return new CacheItem($id);
            }, $ids));

        $items = $this->instance->getItems($ids);

        $this->assertCount(count($ids), $items);
        $this->assertSame($ids, array_values(array_map(function ($item) {
            return $item->getKey();
        }, $items)));
    }

    public function testProxiesDeleteItemCallsToDecoratedCache()
    {
        $id = uniqid(time());

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

    public function testProxiesSaveCallsToDecoratedCache()
    {
        $id = uniqid(time());

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new CacheItem($id));

        /** @var EncryptingItemDecorator $item */
        $item = $this->instance->getItem($id)->set('value');

        $this->decorated->expects($this->once())
            ->method('save')
            ->with($item->getDecorated())
            ->willReturn(true);

        $this->instance->save($item);
    }

    public function testProxiesSaveDeferredCallsToDecoratedCache()
    {
        $id = uniqid(time());

        $this->decorated->expects($this->once())
            ->method('getItem')
            ->with($id)
            ->willReturn(new CacheItem($id));

        /** @var EncryptingItemDecorator $item */
        $item = $this->instance->getItem($id)->set('value');

        $this->decorated->expects($this->once())
            ->method('saveDeferred')
            ->with($item->getDecorated())
            ->willReturn(true);

        $this->instance->saveDeferred($item);
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     *
     * @expectedException \Psr\Cache\InvalidArgumentException
     */
    public function testValidatesItemsProvidedToSave($data)
    {
        $item = (new CacheItem('key'))->set($data);

        $this->instance->save($item);
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     *
     * @expectedException \Psr\Cache\InvalidArgumentException
     */
    public function testValidatesItemsProvidedToSaveDeferred($data)
    {
        $item = (new CacheItem('key'))->set($data);

        $this->instance->saveDeferred($item);
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testDecryptsDataFetchedFromDecoratedCache($data)
    {
        $instance = $this->getInstance(self::$persistentCache);
        $id = uniqid(time());

        $instance->save($instance->getItem($id)->set($data));

        $instance = $this->getInstance(self::$persistentCache);

        $this->assertNotEquals($data, self::$persistentCache->getItem($id)->get());
        $this->assertEquals($data, $instance->getItem($id)->get());
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testReturnsNullWhenFetchRetrievesUnencryptedData($data)
    {
        $instance = $this->getInstance(self::$persistentCache);
        $id = uniqid(time());

        self::$persistentCache
            ->save(self::$persistentCache->getItem($id)->set($data));

        $this->assertEquals($data, self::$persistentCache->getItem($id)->get());
        $this->assertNull($instance->getItem($id)->get());
    }

    public function testHasItemReturnsFalseWhenDecoratedCacheHasNoData()
    {
        $instance = $this->getInstance(self::$persistentCache);
        $id = uniqid(time());

        $this->assertFalse($instance->hasItem($id));
    }

    /**
     * @dataProvider cacheableDataProvider
     *
     * @param mixed $data
     */
    public function testHasItemReturnsFalseWhenKeyHasUnencryptedData($data)
    {
        $instance = $this->getInstance(self::$persistentCache);
        $id = uniqid(time());

        self::$persistentCache
            ->save(self::$persistentCache->getItem($id)->set($data));
        $this->assertTrue(self::$persistentCache->hasItem($id));
        $this->assertFalse($instance->hasItem($id));
    }

    public function createCachePool()
    {
        return $this->getInstance(self::$persistentCache);
    }

    /**
     * @param CacheItemPoolInterface $decorated
     * @return EncryptingPoolDecorator
     */
    abstract protected function getInstance(CacheItemPoolInterface $decorated);
}
