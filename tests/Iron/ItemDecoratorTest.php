<?php
namespace Jsq\CacheEncryption\Iron;

use Jsq\CacheEncryption\ItemDecoratorTest as BaseItemDecoratorTest;
use Jsq\Iron\Iron;
use Jsq\Iron\Password;
use Psr\Cache\CacheItemInterface;

class ItemDecoratorTest extends BaseItemDecoratorTest
{
    use CacheDataProviderTrait;

    public function testAuthenticatesCipherText()
    {
        // This is part of the Iron token specification
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
