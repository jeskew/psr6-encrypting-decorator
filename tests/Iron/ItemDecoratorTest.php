<?php
namespace Jsq\CacheEncryption\Iron;

use Cache\Adapter\Common\CacheItem;
use Jsq\CacheEncryption\ItemDecoratorTest as BaseItemDecoratorTest;
use Iron\Iron;
use Iron\Password;
use Psr\Cache\CacheItemInterface;

class ItemDecoratorTest extends BaseItemDecoratorTest
{
    use CacheDataProviderTrait;

    public function setUp()
    {
        if (!class_exists(Iron::class)) {
            $this->markTestSkipped('The optional Iron-PHP dependency has not been installed');
        }
    }

    public function testAuthenticatesCipherText()
    {
        // This is part of the Iron token specification
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
        $this->getInstance($decorated)->set($data)->finalize();
        

        $this->assertNotEquals($data, $decorated->get());
        $this->assertEquals($data, $this->getInstance($decorated)->get());
    }

    public function cacheableDataProvider()
    {
        $toReturn = [];

        // filter out anything containing objects
        // Iron only supports values that can be round-tripped from JSON
        foreach (parent::cacheableDataProvider() as $data) {
            $containsObjects = false;
            array_walk_recursive($data, function ($leaf) use (&$containsObjects) {
                if (is_object($leaf)) {
                    $containsObjects = true;
                }
            });

            if (!$containsObjects) {
                $toReturn []= $data;
            }
        }

        return $toReturn;
    }

    protected function getInstance(CacheItemInterface $decorated)
    {
        $password = new Password(str_repeat('x', Password::MIN_LENGTH));
        return new ItemDecorator($decorated, $password, Iron::DEFAULT_ENCRYPTION_METHOD);
    }
}
