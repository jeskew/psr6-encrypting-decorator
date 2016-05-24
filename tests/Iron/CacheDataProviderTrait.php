<?php
namespace Jsq\CacheEncryption\Iron;

use Jsq\CacheEncryption\CacheDataProviderTrait as BaseProviderTrait;

trait CacheDataProviderTrait
{
    use BaseProviderTrait {
        cacheableDataProvider as baseCacheableDataProvider;
    }

    public function cacheableDataProvider()
    {
        $toReturn = [];

        // filter out anything containing objects
        // Iron only supports values that can be round-tripped from JSON
        foreach ($this->baseCacheableDataProvider() as $data) {
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
}
